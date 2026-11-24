<?php

namespace App\Http\Controllers\Web\Admin;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Comptabilité (commissions, reversements) et paiements des clients. */
class FinanceController extends AdminController
{
    public function accounting(Request $request)
    {
        $rev = InternalApi::get('revenue/admin', $this->only(['page' => $request->integer('page') ?: null]));
        $withdrawals = InternalApi::get('admin/withdrawal-requests', ['status' => 'pending']);

        return view('web.admin.accounting', [
            'stats' => $rev->json('statistics', []),
            'commissions' => $rev->json('commissions.data', []),
            'meta' => $rev->json('commissions', []),
            'rate' => (float) InternalApi::get('revenue/commission-rate')->json('commission_rate', 10),
            'withdrawals' => $withdrawals->items(),
        ]);
    }

    public function commissionRate(Request $request)
    {
        $request->validate(['commission_rate' => 'required|numeric|min:8|max:10'], ['commission_rate.min' => 'Le taux doit être compris entre 8 et 10 %.', 'commission_rate.max' => 'Le taux doit être compris entre 8 et 10 %.']);
        $res = InternalApi::put('revenue/commission-rate', $request->only('commission_rate'));
        return $res->failBack() ?? back()->with('success', 'Taux de commission enregistré.');
    }

    public function markCommissionsPaid(Request $request)
    {
        $request->validate(['commission_ids' => 'required|array', 'commission_ids.*' => 'integer'], ['commission_ids.required' => 'Cochez au moins une commission.']);
        $res = InternalApi::post('revenue/commissions/mark-paid', $request->only('commission_ids'));
        return $res->failBack() ?? back()->with('success', 'Commissions marquées comme reversées.');
    }

    public function withdrawalAct(Request $request, int $id, string $action)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);
        $res = InternalApi::post("admin/withdrawal-requests/{$id}/{$action}", array_filter(['admin_note' => $request->input('admin_note')]));
        return $res->failBack() ?? back()->with('success', $action === 'approve' ? 'Retrait validé.' : 'Retrait refusé.');
    }

    public function payments(Request $request)
    {
        $status = in_array($request->query('status'), ['completed', 'failed'], true) ? $request->query('status') : null;
        $res = InternalApi::get('admin/payments', $this->only(['status' => $status, 'page' => $request->integer('page') ?: null, 'per_page' => 20]));
        $stuck = InternalApi::get('admin/payments/stuck');

        return view('web.admin.payments', [
            'payments' => $res->json('data', []),
            'meta' => $this->meta($res),
            'summary' => $res->json('summary', []),
            'stuck' => $stuck->json('data', $stuck->items()),
            'status' => $status,
        ]);
    }

    public function paymentAct(Request $request, int $id, string $action)
    {
        if ($action === 'confirm') {
            $request->validate(['transaction_id' => 'required|string|max:255', 'note' => 'nullable|string|max:1000'], ['transaction_id.required' => 'Indiquez la référence de la transaction vue chez le prestataire de paiement.']);
            $res = InternalApi::post("admin/payments/{$id}/confirm-manually", $request->only('transaction_id', 'note'));
        } else {
            $res = InternalApi::post("admin/payments/{$id}/reconcile");
        }
        return $res->failBack() ?? back()->with('success', $res->message('Paiement mis à jour.'));
    }
}
