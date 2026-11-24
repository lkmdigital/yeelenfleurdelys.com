<?php

namespace App\Http\Controllers\Web\Admin;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/**
 * Paramètres généraux. Les clés d'API (WhatsApp, cartes, IA) ne sont pas affichées ici :
 * elles restent modifiables uniquement par l'API, pour ne jamais apparaître dans une page.
 */
class SettingsController extends AdminController
{
    public const TABS = ['general' => 'Général', 'facturation' => 'Facturation et taxes', 'paiements' => 'Moyens de paiement', 'fidelite' => 'Fidélité'];

    private const FIELDS = [
        'general' => [
            'app_name' => 'required|string|max:100',
            'app_support_email' => 'nullable|email|max:255',
            'app_support_phone' => 'nullable|string|max:50',
            'booking_min_nights' => 'required|integer|min:1|max:365',
            'booking_max_nights' => 'required|integer|min:1|max:365',
            'maintenance_enabled' => 'boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ],
        'facturation' => [
            'billing_company_name' => 'nullable|string|max:191',
            'billing_rccm' => 'nullable|string|max:100',
            'billing_ncc' => 'nullable|string|max:100',
            'billing_address' => 'nullable|string|max:500',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'tourist_tax_enabled' => 'boolean',
            'tourist_tax_mode' => 'required|in:fixed,percentage',
            'tourist_tax_amount' => 'required|numeric|min:0',
        ],
        'paiements' => [
            'deferred_payment_enabled' => 'boolean',
        ],
        'fidelite' => [
            'loyalty_points_per_fcfa' => 'required|numeric|min:0',
            'loyalty_first_booking_bonus' => 'required|integer|min:0',
            'loyalty_birthday_bonus' => 'required|integer|min:0',
            'loyalty_review_bonus' => 'required|integer|min:0',
            'loyalty_voucher_validity_days' => 'required|integer|min:0',
        ],
    ];

    public function index(?string $tab = null)
    {
        $tab = array_key_exists((string) $tab, self::TABS) ? $tab : 'general';
        $all = InternalApi::get('settings/admin')->json();

        return view('web.admin.settings', [
            'tab' => $tab,
            'tabs' => self::TABS,
            's' => array_intersect_key($all, array_merge(...array_values(self::FIELDS))),
            'methods' => $tab === 'paiements' ? InternalApi::get('admin/payment-methods')->json('data', []) : [],
        ]);
    }

    public function update(Request $request)
    {
        $tab = $request->input('tab');
        abort_unless(array_key_exists((string) $tab, self::FIELDS), 404);
        $rules = self::FIELDS[$tab];
        $data = $request->validate($rules);
        foreach ($rules as $k => $r) {
            if ($r === 'boolean') {
                $data[$k] = $request->boolean($k);
            }
        }
        if (array_key_exists('maintenance_message', $data) && $data['maintenance_message'] === null) {
            $data['maintenance_message'] = '';
        }
        $res = InternalApi::put('settings/admin', $data);
        return $res->failBack() ?? back()->with('success', 'Paramètres enregistrés.');
    }

    public function paymentMethod(Request $request, int $id)
    {
        $res = InternalApi::put("admin/payment-methods/{$id}", ['is_active' => $request->boolean('is_active')]);
        return $res->failBack() ?? back()->with('success', 'Moyen de paiement mis à jour.');
    }
}
