<?php

namespace App\Http\Controllers\Web\Admin;

use App\Support\InternalApi;

class DashboardController extends AdminController
{
    public function index()
    {
        $stats = InternalApi::get('admin/dashboard/stats');
        $overview = InternalApi::get('admin/accommodations/overview');
        $pending = InternalApi::get('admin/bookings', ['status' => 'awaiting_host_confirmation', 'per_page' => 5]);
        $trend = InternalApi::get('admin/dashboard/monthly-revenue-trend');

        return view('web.admin.dashboard', [
            's' => $stats->json('data', []),
            'establishments' => $overview->json('data', []),
            'pending' => $pending->json('data', []),
            'pendingTotal' => (int) $pending->json('pagination.total', 0),
            'trend' => $trend->json('data', []),
        ]);
    }

    public function analytics()
    {
        $a = InternalApi::get('analytics/admin');
        return view('web.admin.analytics', [
            'a' => $a->json(),
            's' => InternalApi::get('admin/dashboard/stats')->json('data', []),
            'trend' => InternalApi::get('admin/dashboard/monthly-revenue-trend')->json('data', []),
            'occupancy' => InternalApi::get('admin/dashboard/occupancy-trend')->json('data', []),
            'top' => InternalApi::get('admin/dashboard/top-accommodations')->json('data', []),
        ]);
    }
}
