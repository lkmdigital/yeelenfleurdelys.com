<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Vue d'ensemble du mois : une ligne par chambre, une case par jour. */
class CalendarController extends HostController
{
    public function index(Request $request)
    {
        $m = $request->query('month');
        $month = $m && preg_match('/^\d{4}-\d{2}$/', $m) ? \Carbon\Carbon::createFromFormat('Y-m-d', "$m-01")->startOfDay() : now()->startOfMonth();
        $base = 'accommodations/' . $this->propertyId() . '/rooms';
        $rooms = InternalApi::get("$base/manage")->items();

        $grid = [];
        foreach ($rooms as $r) {
            $cal = InternalApi::get("$base/{$r['id']}/calendar", [
                'start_date' => $month->copy()->startOfMonth()->toDateString(),
                'end_date' => $month->copy()->endOfMonth()->toDateString(),
            ]);
            $grid[$r['id']] = collect($cal->json('calendar', []))->keyBy('date')->all();
        }

        // Arrivées du mois (réservations confirmées ou en attente), pour repérer les nuits vendues.
        $bookings = InternalApi::get('bookings', [
            'from_date' => $month->copy()->startOfMonth()->subDays(31)->toDateString(),
            'to_date' => $month->copy()->endOfMonth()->toDateString(),
            'per_page' => 200,
        ])->items();
        $sold = [];
        foreach ($bookings as $b) {
            if (!in_array($b['status'], ['confirmed', 'pending', 'completed', 'awaiting_host_confirmation'], true) || empty($b['room_id'])) {
                continue;
            }
            $d = \Carbon\Carbon::parse(substr($b['check_in'], 0, 10));
            $end = \Carbon\Carbon::parse(substr($b['check_out'], 0, 10));
            for (; $d->lt($end); $d->addDay()) {
                $sold[$b['room_id']][$d->toDateString()] = ($sold[$b['room_id']][$d->toDateString()] ?? 0) + (int) ($b['rooms_quantity'] ?? 1);
            }
        }

        return $this->page('calendar', compact('month', 'rooms', 'grid', 'sold'));
    }
}
