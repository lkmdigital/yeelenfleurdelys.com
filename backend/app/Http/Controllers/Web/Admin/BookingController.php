<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Host\BookingController as HostBookings;
use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Réservations de tous les établissements. */
class BookingController extends AdminController
{
    public function index(Request $request)
    {
        $isDate = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
        $filters = $this->only([
            'search' => trim((string) $request->query('search')),
            'status' => array_key_exists((string) $request->query('status'), HostBookings::STATUSES) ? $request->query('status') : null,
            'payment_status' => array_key_exists((string) $request->query('payment_status'), HostBookings::PAYMENTS) ? $request->query('payment_status') : null,
            'accommodation_id' => $request->integer('accommodation_id') ?: null,
            'from_date' => $isDate($request->query('from_date')) ? $request->query('from_date') : null,
            'to_date' => $isDate($request->query('to_date')) ? $request->query('to_date') : null,
        ]);
        $res = InternalApi::get('admin/bookings', $filters + ['page' => $request->integer('page') ?: 1, 'per_page' => 20]);
        $accs = InternalApi::get('admin/accommodations/overview')->json('data', []);

        return view('web.admin.bookings.index', [
            'bookings' => $res->json('data', []),
            'meta' => $this->meta($res),
            'filters' => $filters,
            'establishments' => collect($accs)->pluck('name', 'id')->all(),
        ]);
    }

    public function show(int $id)
    {
        $res = InternalApi::get("admin/bookings/{$id}");
        abort_unless($res->ok(), 404);
        return view('web.admin.bookings.show', ['b' => $res->json('data')]);
    }

    public function act(Request $request, int $id, string $action)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $res = InternalApi::post("bookings/{$id}/{$action}", array_filter(['reason' => $request->input('reason')]));
        $done = ['approve' => 'Disponibilité confirmée pour le client.', 'refuse' => 'Demande refusée.', 'cancel' => 'Réservation annulée.', 'complete' => 'Séjour marqué comme terminé.'][$action];
        return $res->failBack() ?? back()->with('success', $done);
    }
}
