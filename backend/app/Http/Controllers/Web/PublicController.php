<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Room;
use App\Support\InternalApi;
use App\Support\Nav;
use Illuminate\Http\Request;

/** Pages publiques : accueil, établissements, chambres, aide et pages légales. */
class PublicController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()?->isHost()) {
            return redirect()->route('host.dashboard');
        }

        $res = InternalApi::get('accommodations', ['per_page' => 50]);

        return view('web.public.home', [
            'items' => $res->items(),
            'failed' => !$res->ok(),
        ]);
    }

    public function accommodations(Request $request)
    {
        $query = array_filter([
            'per_page' => 50,
            'city' => $request->query('city'),
            'check_in' => $request->query('check_in'),
            'check_out' => $request->query('check_out'),
            'guests' => $request->query('guests'),
        ]);
        $res = InternalApi::get('accommodations', $query);

        return view('web.public.accommodations', [
            'items' => $res->items(),
            'failed' => !$res->ok(),
            'cities' => collect(InternalApi::get('accommodations', ['per_page' => 50])->items())->pluck('city')->filter()->unique()->sort()->values(),
        ]);
    }

    public function accommodation(Request $request, string $id)
    {
        $res = InternalApi::get("accommodations/{$id}");
        abort_unless($res->ok(), $res->status() === 404 ? 404 : 503);
        $acc = $res->json();

        $search = $this->searchParams($request);
        $rooms = InternalApi::get("accommodations/{$acc['id']}/rooms", array_filter($search))->json();
        $rooms = is_array($rooms) && array_is_list($rooms) ? $rooms : [];
        usort($rooms, fn ($a, $b) => ($a['effective_price_per_night'] ?? $a['price_per_night']) <=> ($b['effective_price_per_night'] ?? $b['price_per_night']));

        // Chambres créées sans tarif (en attente du gérant) : montrées « tarif sur demande »,
        // sans réservation en ligne. Une chambre désactivée qui a un prix reste masquée.
        $onRequest = Room::where('accommodation_id', $acc['id'])
            ->where('is_active', false)
            ->where(fn ($q) => $q->whereNull('price_per_night')->orWhere('price_per_night', '<=', 0))
            ->get()
            ->map(fn (Room $r) => ['id' => $r->id, 'name' => $r->name ?: $r->type, 'capacity' => $r->capacity, 'surface_area' => $r->surface_area, 'amenities' => (array) $r->amenities])
            ->all();

        [$in, $out] = $search['check_in'] && $search['check_out']
            ? [$search['check_in'], $search['check_out']]
            : [now()->addDay()->toDateString(), now()->addDays(2)->toDateString()];
        $quote = InternalApi::get("accommodations/{$acc['id']}/price-preview", ['check_in' => $in, 'check_out' => $out]);

        $reviews = InternalApi::get("accommodations/{$acc['id']}/reviews");
        $favorite = $request->user()
            ? in_array($acc['id'], (array) InternalApi::get('favorites/ids')->json('ids', []), false)
            : false;

        return view('web.public.accommodation', [
            'acc' => $acc,
            'rooms' => $rooms,
            'onRequest' => $onRequest,
            'search' => $search,
            'quote' => $quote->ok() ? $quote->json() : null,
            'reviews' => $reviews->ok() ? $reviews->items() : [],
            'favorite' => $favorite,
        ]);
    }

    public function room(Request $request, string $id)
    {
        $res = InternalApi::get("rooms/{$id}");
        abort_unless($res->ok(), $res->status() === 404 ? 404 : 503);
        $room = $res->json();
        $acc = InternalApi::get("accommodations/{$room['accommodation_id']}")->json();

        $search = $this->searchParams($request);
        $quote = null;
        if ($search['check_in'] && $search['check_out']) {
            $q = InternalApi::get("accommodations/{$room['accommodation_id']}/price-preview", [
                'check_in' => $search['check_in'], 'check_out' => $search['check_out'], 'room_id' => $room['id'],
            ]);
            $quote = $q->ok() ? $q->json() : null;
        }

        return view('web.public.room', compact('room', 'acc', 'search', 'quote'));
    }

    public function legal(string $slug)
    {
        $res = InternalApi::get("legal/{$slug}");
        $titles = ['cgu' => "Conditions générales d'utilisation", 'cgv' => 'Conditions générales de vente', 'confidentialite' => 'Politique de confidentialité'];

        return view('web.public.legal', [
            'doc' => $res->ok() ? $res->json() : null,
            'title' => $titles[$slug] ?? 'Document',
        ]);
    }

    public function faq()
    {
        return view('web.public.faq', ['groups' => $this->support()['faq'], 'support' => $this->support()['support']]);
    }

    public function help()
    {
        return view('web.public.help', ['support' => $this->support()['support']]);
    }

    public function contact()
    {
        return view('web.public.contact', ['support' => $this->support()['support']]);
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|min:10|max:3000',
        ], [
            'message.min' => 'Votre message doit contenir au moins 10 caractères.',
        ]);

        $res = InternalApi::post('contact', $request->only('name', 'email', 'subject', 'message', 'website'));
        if ($fail = $res->failBack()) {
            return $fail;
        }
        return back()->with('success', 'Merci ! Votre message a bien été envoyé. Nous vous répondons rapidement.');
    }

    public function lookup()
    {
        return view('web.public.lookup');
    }

    public function doLookup(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:64',
            'email' => 'required|email|max:255',
        ], ['reference.required' => 'Indiquez votre numéro de réservation.']);

        $res = InternalApi::post('bookings/lookup', $request->only('reference', 'email'));
        if (!$res->ok()) {
            return back()->withErrors(['reference' => $res->message()])->withInput();
        }
        return redirect()->route('bookings.show', $res->json('access_token'));
    }

    public function reviewByToken(string $token)
    {
        $res = InternalApi::get("reviews/booking-by-token/{$token}");

        return view('web.public.review-token', [
            'token' => $token,
            'booking' => $res->ok() ? $res->json() : null,
            'problem' => $res->ok() ? null : $res->message('Lien invalide ou expiré.'),
            'categories' => Review::CATEGORIES,
        ]);
    }

    public function submitReviewByToken(Request $request, string $token)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ], [
            'rating.required' => 'Choisissez une note de 1 à 5.',
            'comment.required' => 'Écrivez quelques mots sur votre séjour.',
        ]);

        $res = InternalApi::post('reviews/submit-by-token', [
            'token' => $token,
            'rating' => (int) $request->input('rating'),
            'comment' => $request->input('comment'),
            'category_ratings' => array_filter((array) $request->input('category_ratings', [])),
        ]);
        if ($fail = $res->failBack()) {
            return $fail;
        }
        return redirect()->route('review.token', $token)->with('success', 'Merci pour votre avis ! Il sera publié après relecture.');
    }

    /** Ancien lien de paiement : le jeton est la référence de la réservation. */
    public function payByToken(string $token)
    {
        return redirect()->route('bookings.payment', $token);
    }

    public function favorites(Request $request)
    {
        if (!$request->user()) {
            return redirect()->route('login', ['redirect' => '/favorites']);
        }
        return redirect()->route('client.favorites');
    }

    public function toggleFavorite(Request $request, int $accommodation)
    {
        $ids = (array) InternalApi::get('favorites/ids')->json('ids', []);
        $on = in_array($accommodation, $ids, false);
        $res = $on
            ? InternalApi::delete("favorites/{$accommodation}")
            : InternalApi::post('favorites', ['accommodation_id' => $accommodation]);

        if ($fail = $res->failBack()) {
            return $fail;
        }
        return back()->with('success', $on ? 'Retiré de vos favoris.' : 'Ajouté à vos favoris.');
    }

    // ────────────────────────────────────────────────────────────────────

    /** Dates et voyageurs saisis (valides uniquement si arrivée ≥ aujourd'hui et départ > arrivée). */
    private function searchParams(Request $request): array
    {
        $in = $request->query('check_in');
        $out = $request->query('check_out');
        $valid = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
        if (!$valid($in) || !$valid($out) || $in < now()->toDateString() || $out <= $in) {
            $in = $out = null;
        }
        return [
            'check_in' => $in,
            'check_out' => $out,
            'guests' => max(1, min(20, (int) $request->query('guests', 2))),
        ];
    }

    private function support(): array
    {
        static $data;
        return $data ??= json_decode(file_get_contents(resource_path('data/support.json')), true);
    }
}
