<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\InternalApi;
use Illuminate\Http\Request;

/**
 * Parcours de réservation public : demande, paiement, suivi et annulation.
 * La référence d'une réservation dans l'adresse est son jeton d'accès (ou l'id pour le
 * client connecté) : c'est l'API qui décide qui peut la voir.
 */
class BookingController extends Controller
{
    public function create(Request $request)
    {
        $roomId = $request->integer('room_id') ?: null;
        $room = null;
        if ($roomId) {
            $r = InternalApi::get("rooms/{$roomId}");
            abort_unless($r->ok(), 404);
            $room = $r->json();
        }
        $accId = $room['accommodation_id'] ?? $request->integer('accommodation_id');
        abort_unless($accId, 404);
        $acc = InternalApi::get("accommodations/{$accId}");
        abort_unless($acc->ok(), 404);
        $acc = $acc->json();

        $in = $request->query('check_in');
        $out = $request->query('check_out');
        $datesOk = $this->isDate($in) && $this->isDate($out) && $in >= now()->toDateString() && $out > $in;
        if (!$datesOk) {
            $in = now()->addDay()->toDateString();
            $out = now()->addDays(2)->toDateString();
        }
        $guests = max(1, min(20, $request->integer('guests', 2)));
        $roomsQty = max(1, min(20, $request->integer('rooms_quantity', 1)));

        $quote = InternalApi::get("accommodations/{$accId}/price-preview", array_filter([
            'check_in' => $in, 'check_out' => $out, 'room_id' => $roomId, 'rooms_quantity' => $roomsQty > 1 ? $roomsQty : null,
        ]));

        return view('web.booking.create', [
            'acc' => $acc,
            'room' => $room,
            'search' => ['check_in' => $in, 'check_out' => $out, 'guests' => $guests, 'rooms_quantity' => $roomsQty, 'dates_ok' => $datesOk],
            'quote' => $quote->ok() ? $quote->json() : null,
            'quoteError' => $quote->ok() ? null : $quote->message(),
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $rules = [
            'accommodation_id' => 'required|integer',
            'room_id' => 'nullable|integer',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'phone' => ['required', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'residence_country' => 'required|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
            'accept_terms' => 'accepted',
        ];
        if (!$user) {
            $rules += ['first_name' => 'required|string|max:120', 'last_name' => 'required|string|max:120', 'email' => 'required|email|max:255'];
        }
        if ($request->boolean('booked_for_third_party')) {
            $rules += ['traveler_name' => 'required|string|max:255', 'traveler_phone' => ['required', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/']];
        }
        $request->validate($rules, [
            'phone.required' => 'Indiquez un numéro de téléphone pour être joint par l’établissement.',
            'phone.regex' => 'Le numéro de téléphone ne doit contenir que des chiffres.',
            'residence_country.required' => 'Indiquez votre pays de résidence.',
            'accept_terms.accepted' => 'Vous devez accepter les conditions générales de vente.',
            'check_out.after' => 'La date de départ doit être après la date d’arrivée.',
        ]);

        $payload = array_filter([
            'accommodation_id' => $request->integer('accommodation_id'),
            'room_id' => $request->integer('room_id') ?: null,
            'check_in' => $request->input('check_in'),
            'check_out' => $request->input('check_out'),
            'guests' => $request->integer('guests'),
            'children' => $request->integer('children') ?: null,
            'rooms_quantity' => $request->integer('rooms_quantity') > 1 ? $request->integer('rooms_quantity') : null,
            'estimated_arrival_time' => $request->input('estimated_arrival_time') ?: null,
            'traveler_type' => 'individual',
            'residence_country' => $request->input('residence_country'),
            'residence_city' => $request->input('residence_city') ?: null,
            'promo_code' => trim((string) $request->input('promo_code')) ?: null,
            'special_requests' => trim((string) $request->input('special_requests')) ?: null,
            'phone' => $request->input('phone'),
        ], fn ($v) => $v !== null);
        $payload['booked_for_third_party'] = $request->boolean('booked_for_third_party');
        if (!$user) {
            $payload['name'] = trim($request->input('first_name') . ' ' . $request->input('last_name'));
            $payload['email'] = $request->input('email');
        }
        if ($payload['booked_for_third_party']) {
            $payload['traveler_name'] = $request->input('traveler_name');
            $payload['traveler_phone'] = $request->input('traveler_phone');
        }

        $res = InternalApi::post('bookings', $payload);
        if (!$res->ok()) {
            return back()->withErrors($res->errors())->withInput();
        }

        $ref = $res->json('access_token') ?? $res->json('id');
        if ($res->json('status') === 'awaiting_host_confirmation') {
            return redirect()->route('bookings.show', $ref)
                ->with('success', 'Votre demande est envoyée. L’établissement confirme la disponibilité, puis vous recevez le lien de paiement.');
        }
        return redirect()->route('bookings.payment', $ref);
    }

    public function success(Request $request)
    {
        $ref = (string) $request->query('id');
        $res = $ref !== '' ? InternalApi::get("bookings/{$ref}") : null;
        if (!$res?->ok()) {
            return redirect()->route('home');
        }
        return view('web.booking.success', ['b' => $res->json(), 'ref' => $ref]);
    }

    public function index(Request $request)
    {
        return $request->user() ? redirect()->route('client.reservations') : redirect()->route('booking.lookup');
    }

    public function show(Request $request, string $ref)
    {
        $res = InternalApi::get("bookings/{$ref}");
        if (!$res->ok()) {
            return view('web.booking.unavailable', ['status' => $res->status(), 'message' => $res->message()]);
        }
        $b = $res->json();
        $messages = $request->user() ? InternalApi::get("bookings/{$b['id']}/messages") : null;

        return view('web.booking.show', [
            'b' => $b,
            'ref' => $ref,
            'messages' => $messages?->ok() ? ($messages->json('data') ?? $messages->items()) : null,
        ]);
    }

    public function cancelForm(Request $request, string $ref)
    {
        $res = InternalApi::get("bookings/{$ref}");
        if (!$res->ok()) {
            return view('web.booking.unavailable', ['status' => $res->status(), 'message' => $res->message()]);
        }
        $b = $res->json();
        $own = $request->user() && ($b['user_id'] ?? null) === $request->user()->id;

        return view('web.booking.cancel', ['b' => $b, 'ref' => $ref, 'own' => $own]);
    }

    public function cancel(Request $request, string $ref)
    {
        $res = InternalApi::get("bookings/{$ref}");
        abort_unless($res->ok(), 404);
        $b = $res->json();
        $own = $request->user() && ($b['user_id'] ?? null) === $request->user()->id;

        if ($own) {
            $request->validate(['reason' => 'nullable|string|max:500']);
            $r = InternalApi::post("bookings/{$b['id']}/cancel", ['reason' => $request->input('reason')]);
        } else {
            $request->validate(['email' => 'required|email|max:255'], ['email.required' => 'Indiquez l’adresse e-mail utilisée pour réserver.']);
            $r = InternalApi::post("bookings/{$ref}/guest-cancel", ['email' => $request->input('email')]);
        }
        if ($fail = $r->failBack()) {
            return $fail;
        }
        return redirect()->route('bookings.show', $ref)->with('success', $r->message('Votre réservation a été annulée.'));
    }

    public function payment(Request $request, string $ref)
    {
        $res = InternalApi::get("bookings/{$ref}");
        if (!$res->ok()) {
            return view('web.booking.unavailable', ['status' => $res->status(), 'message' => $res->message()]);
        }
        $b = $res->json();
        if (in_array($b['payment_status'] ?? null, ['paid'], true) || ($b['status'] ?? null) === 'awaiting_host_confirmation'
            || in_array($b['status'] ?? null, ['cancelled', 'rejected', 'completed'], true)) {
            return redirect()->route('bookings.show', $ref);
        }

        $quote = InternalApi::get("accommodations/{$b['accommodation_id']}/price-preview", array_filter([
            'check_in' => substr($b['check_in'], 0, 10),
            'check_out' => substr($b['check_out'], 0, 10),
            'room_id' => $b['room_id'] ?? null,
            'rooms_quantity' => ($b['rooms_quantity'] ?? 1) > 1 ? $b['rooms_quantity'] : null,
        ]));

        return view('web.booking.payment', [
            'b' => $b,
            'ref' => $ref,
            'methods' => InternalApi::get('payment-methods')->json() ?: [],
            'options' => $quote->ok() ? $quote->json('payment_options') : null,
            'waiting' => session('waiting_payment'),
        ]);
    }

    public function pay(Request $request, string $ref)
    {
        if ($request->filled('otp_code')) {
            $r = InternalApi::post("bookings/{$ref}/payment/peyapay/verify", ['otp_code' => trim((string) $request->input('otp_code'))]);
            if ($r->ok() && $r->json('status') === 'completed') {
                return redirect()->route('bookings.success', ['id' => $ref]);
            }
            return back()->with($r->ok() ? 'waiting_payment' : 'error', $r->ok() ? session('waiting_payment') : $r->message("Le code n'a pas pu être vérifié."));
        }

        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_type' => 'required|in:full,guarantee',
        ], ['payment_method.required' => 'Choisissez un moyen de paiement.']);

        $res = InternalApi::post("bookings/{$ref}/payment/initiate", $request->only('payment_method', 'payment_type'));
        if (!$res->ok()) {
            return back()->with('error', $res->message('Le paiement n’a pas pu être lancé. Réessayez.'))->withInput();
        }

        if ($res->json('awaiting_phone_validation') || $res->json('requires_otp')) {
            return back()->with('waiting_payment', [
                'id' => $res->json('payment.id'),
                'mode' => $res->json('requires_otp') ? 'otp' : 'phone',
                'since' => now()->timestamp,
            ]);
        }

        $link = $res->json('link') ?: $res->json('payment_url');
        if (!$link && $res->json('payment.id')) {
            $p = InternalApi::post('payments/' . $res->json('payment.id') . '/process', ['payment_method' => $request->input('payment_method')]);
            $link = $p->json('link') ?: $p->json('payment_url') ?: $p->json('redirect_url');
        }
        if (!$link) {
            return back()->with('error', 'Le lien de paiement est indisponible pour le moment. Réessayez dans quelques instants.');
        }
        return redirect()->away($link);
    }

    /** Suivi d'un paiement Mobile Money validé sur le téléphone (appelé toutes les 5 s par la page). */
    public function paymentStatus(Request $request, string $ref)
    {
        $paymentId = $request->integer('payment');
        $res = InternalApi::get("payments/{$paymentId}");
        return response()->json([
            'status' => $res->ok() ? $res->json('status') : null,
            'success_url' => route('bookings.success', ['id' => $ref]),
        ]);
    }

    public function message(Request $request, string $ref)
    {
        $request->validate(['body' => 'required|string|max:2000'], ['body.required' => 'Écrivez votre message.']);
        $res = InternalApi::get("bookings/{$ref}");
        abort_unless($res->ok(), 404);
        $r = InternalApi::post('bookings/' . $res->json('id') . '/messages', ['body' => $request->input('body')]);
        if ($fail = $r->failBack()) {
            return $fail;
        }
        return back()->with('success', 'Message envoyé à l’établissement.');
    }

    private function isDate(mixed $d): bool
    {
        return is_string($d) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
    }
}
