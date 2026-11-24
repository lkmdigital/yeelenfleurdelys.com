<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;

class DashboardController extends HostController
{
    public function index()
    {
        $id = $this->propertyId();
        $stats = InternalApi::get('analytics/host');
        $overview = InternalApi::get('bookings/host/overview');
        $requests = InternalApi::get('bookings', ['status' => 'awaiting_host_confirmation', 'per_page' => 5]);
        $readiness = InternalApi::get("accommodations/{$id}/readiness");

        return $this->page('dashboard', [
            'stats' => $stats->ok() ? $stats->json() : null,
            'arrivals' => $overview->ok() ? $overview->json('week', []) : [],
            'upcoming' => $overview->ok() ? $overview->json('month', []) : [],
            'requests' => $requests->ok() ? $requests->items() : [],
            'requestsTotal' => (int) $requests->json('total', 0),
            'readiness' => $readiness->ok() ? $readiness->json() : null,
        ]);
    }
}
