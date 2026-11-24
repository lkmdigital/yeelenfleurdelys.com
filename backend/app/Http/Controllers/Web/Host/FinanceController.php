<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Finances (reversements), statistiques et programme fidélité. */
class FinanceController extends HostController
{
    public function finances(Request $request)
    {
        $this->property();
        $rev = InternalApi::get('revenue/host', array_filter(['page' => $request->integer('page') ?: null]));
        $withdrawals = InternalApi::get('host/withdrawal-requests');

        return $this->page('finances', [
            'denied' => $rev->status() === 403,
            'stats' => $rev->json('statistics', []),
            'monthly' => array_reverse($rev->json('monthly_revenue', [])),
            'revenues' => $rev->json('revenues.data', []),
            'meta' => $rev->json('revenues', []),
            'withdrawals' => $withdrawals->ok() ? $withdrawals->items() : [],
            'balance' => (float) InternalApi::get('host/withdrawal-requests/balance')->json('available_balance', 0),
            'isOwner' => !auth()->user()->isStaff(),
        ]);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'nullable|string|max:50',
            'host_note' => 'nullable|string|max:500',
        ], ['amount.min' => 'Le montant minimum d’un retrait est de 1 000 FCFA.']);
        $res = InternalApi::post('host/withdrawal-requests', $request->only('amount', 'payment_method', 'host_note'));
        return $res->failBack() ?? back()->with('success', 'Demande de retrait envoyée. Elle sera traitée par l’administration.');
    }

    public function stats()
    {
        $this->property();
        $res = InternalApi::get('analytics/host');
        return $this->page('stats', ['s' => $res->ok() ? $res->json() : null]);
    }

    public function programme()
    {
        $acc = $this->property();
        $res = InternalApi::get('host/loyalty/stats');
        return $this->page('programme', ['loyalty' => $res->json('data', []), 'acc' => $acc]);
    }

    public function updateProgramme(Request $request)
    {
        $join = $request->boolean('join');
        $res = InternalApi::put('accommodations/' . $this->propertyId(), ['loyalty_program_joined' => $join]);
        return $res->failBack() ?? back()->with('success', $join ? 'Votre établissement participe au programme fidélité.' : 'Votre établissement ne participe plus au programme fidélité.');
    }
}
