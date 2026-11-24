<?php

namespace App\Http\Controllers\Web\Host;

use App\Models\HostStaff;
use App\Support\InternalApi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Comptes des collaborateurs de l'établissement (réception, comptabilité…). */
class StaffController extends HostController
{
    public function index()
    {
        $this->property();
        $res = InternalApi::get('host/staff');

        return $this->page('staff', [
            'staff' => $res->ok() ? $res->items() : [],
            'denied' => $res->status() === 403,
            'deniedMessage' => $res->status() === 403 ? $res->message() : null,
            'roles' => HostStaff::ROLE_LABELS,
            'permissions' => array_intersect_key(HostStaff::PERMISSION_LABELS, array_flip(['property', 'rooms', 'calendar', 'reservations', 'clients', 'reviews', 'promotions', 'finances', 'staff', 'stats'])),
            'defaults' => HostStaff::DEFAULT_PERMISSIONS_BY_ROLE,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(HostStaff::ROLES)],
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::in(HostStaff::PERMISSIONS)],
        ], ['email.required' => 'Indiquez l’adresse e-mail du collaborateur.']);
        $res = InternalApi::post('host/staff', $data + ['permissions' => []]);
        return $res->failBack() ?? back()->with('success', 'Invitation envoyée par e-mail à ' . $data['email'] . '.');
    }

    public function update(Request $request, int $staff)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => ['nullable', Rule::in(HostStaff::ROLES)],
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::in(HostStaff::PERMISSIONS)],
            'status' => ['nullable', Rule::in([HostStaff::STATUS_ACTIVE, HostStaff::STATUS_SUSPENDED])],
        ]);
        if ($request->has('role')) {
            $data['permissions'] = $data['permissions'] ?? [];
        }
        $res = InternalApi::put("host/staff/{$staff}", array_filter($data, fn ($v) => $v !== null));
        return $res->failBack() ?? back()->with('success', 'Accès du collaborateur mis à jour.');
    }

    public function destroy(int $staff)
    {
        $res = InternalApi::delete("host/staff/{$staff}");
        return $res->failBack() ?? back()->with('success', 'Accès du collaborateur supprimé.');
    }
}
