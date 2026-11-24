<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Réservations, demandes à confirmer, clients et messages de l'établissement. */
class BookingController extends HostController
{
    public const STATUSES = [
        'all' => 'Tous les statuts',
        'awaiting_host_confirmation' => 'À confirmer',
        'pending' => 'En attente de paiement',
        'confirmed' => 'Confirmées',
        'in_progress' => 'Séjour en cours',
        'completed' => 'Terminées',
        'cancelled' => 'Annulées',
        'rejected' => 'Refusées',
    ];

    public const PAYMENTS = [
        'all' => 'Tous les paiements',
        'paid' => 'Payé',
        'partial' => 'Garantie payée',
        'pending' => 'Non payé',
        'refunded' => 'Remboursé',
    ];

    public function index(Request $request)
    {
        $this->property();
        $filters = $this->filters($request);
        $res = InternalApi::get('bookings', $filters + ['per_page' => 15, 'page' => $request->integer('page') ?: 1]);

        return $this->page('bookings.index', [
            'bookings' => $res->items(),
            'meta' => $res->json(),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request)
    {
        $this->property();
        $res = InternalApi::get('bookings/host/export', $this->filters($request));
        if (!$res->ok()) {
            return back()->with('error', $res->message("L'export n'a pas pu être généré."));
        }
        return $res->response;
    }

    public function requests(Request $request)
    {
        $this->property();
        $res = InternalApi::get('bookings', ['status' => 'awaiting_host_confirmation', 'per_page' => 20, 'page' => $request->integer('page') ?: 1]);

        return $this->page('bookings.requests', ['bookings' => $res->items(), 'meta' => $res->json()]);
    }

    public function show(int $booking)
    {
        $this->property();
        $res = InternalApi::get("bookings/{$booking}");
        abort_unless($res->ok(), 404);
        $messages = InternalApi::get("bookings/{$booking}/messages");

        return $this->page('bookings.show', [
            'b' => $res->json(),
            'messages' => $messages->ok() ? ($messages->json('data') ?? $messages->items()) : [],
        ]);
    }

    public function act(Request $request, int $booking, string $action)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $res = InternalApi::post("bookings/{$booking}/{$action}", array_filter(['reason' => $request->input('reason')]));
        if ($fail = $res->failBack()) {
            return $fail;
        }
        $done = [
            'approve' => 'Disponibilité confirmée. Le client reçoit le lien de paiement.',
            'refuse' => 'Demande refusée. Le client est prévenu.',
            'cancel' => 'Réservation annulée. Le client est prévenu.',
            'complete' => 'Séjour marqué comme terminé.',
        ][$action];
        return back()->with('success', $done);
    }

    public function message(Request $request, int $booking)
    {
        $request->validate(['body' => 'required|string|max:2000'], ['body.required' => 'Écrivez votre message.']);
        $res = InternalApi::post("bookings/{$booking}/messages", ['body' => $request->input('body')]);
        return $res->failBack() ?? back()->with('success', 'Message envoyé au client.');
    }

    public function checkIn(Request $request)
    {
        $request->validate(['confirmation_code' => 'required|string|size:8'], [
            'confirmation_code.required' => 'Saisissez le code à 8 caractères présenté par le client.',
            'confirmation_code.size' => 'Le code d’arrivée contient 8 caractères.',
        ]);
        $res = InternalApi::post('host/check-in', ['confirmation_code' => strtoupper(trim($request->input('confirmation_code')))]);
        return $res->failBack() ?? back()->with('success', $res->message('Arrivée du client enregistrée.'));
    }

    public function clients(Request $request)
    {
        $this->property();
        $search = trim((string) $request->query('search'));
        $res = InternalApi::get('host/clients', array_filter(['search' => $search, 'page' => $request->integer('page') ?: null]));

        return $this->page('clients', ['clients' => $res->items(), 'meta' => $res->json(), 'search' => $search, 'denied' => $res->status() === 403]);
    }

    public function inbox()
    {
        $this->property();
        $res = InternalApi::get('host/inbox');
        return $this->page('inbox', ['threads' => $res->items(), 'meta' => $res->json(), 'me' => auth()->id()]);
    }

    public function inboxReply(Request $request)
    {
        $request->validate(['parent_id' => 'required|integer', 'body' => 'required|string|max:2000'], ['body.required' => 'Écrivez votre réponse.']);
        $res = InternalApi::post('host/inbox', $request->only('parent_id', 'body'));
        if ($res->ok()) {
            InternalApi::patch('host/inbox/' . $request->integer('parent_id') . '/read');
        }
        return $res->failBack() ?? back()->with('success', 'Réponse envoyée.');
    }

    private function filters(Request $request): array
    {
        $status = $request->query('status');
        $payment = $request->query('payment_status');
        $from = $request->query('from_date');
        $to = $request->query('to_date');
        $isDate = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);

        return array_filter([
            'status' => array_key_exists((string) $status, self::STATUSES) && $status !== 'all' ? $status : null,
            'payment_status' => array_key_exists((string) $payment, self::PAYMENTS) && $payment !== 'all' ? $payment : null,
            'from_date' => $isDate($from) ? $from : null,
            'to_date' => $isDate($to) ? $to : null,
            'search' => trim((string) $request->query('search')) ?: null,
        ]);
    }
}
