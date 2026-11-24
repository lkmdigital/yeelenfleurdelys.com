@php use App\Support\Fmt; @endphp
<x-layouts.panel space="admin" title="Comptabilité" heading="Comptabilité">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-kpi label="Commissions (total)" :value="Fmt::price($stats['total_commissions'] ?? 0)" icon="wallet" :hint="($stats['total_bookings'] ?? 0) . ' réservation(s) payée(s)'" />
        <x-kpi label="Commissions du mois" :value="Fmt::price($stats['monthly_commissions'] ?? 0)" icon="calendar" />
        <x-kpi label="À reverser aux établissements" :value="Fmt::price($stats['pending_commissions'] ?? 0)" icon="clock" hint="Commissions en attente" />
        <x-kpi label="Déjà réglé" :value="Fmt::price($stats['paid_commissions'] ?? 0)" icon="check-circle" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
        <section>
            <h2 class="section-title mb-3">Demandes de retrait en attente</h2>
            <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
                @forelse ($withdrawals as $w)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                        <div><span class="font-semibold">{{ $w['host']['name'] ?? 'Gérant' }}</span> · {{ Fmt::price($w['amount']) }}<span class="block text-xs text-gray-500">{{ Fmt::date($w['created_at']) }} · {{ $w['payment_method'] === 'mobile_money' ? 'Mobile Money' : 'Virement' }}@if ($w['host_note']) · « {{ $w['host_note'] }} »@endif</span></div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.withdrawals.act', [$w['id'], 'approve']) }}">@csrf<button class="btn-primary btn-sm" type="submit">Valider (versé)</button></form>
                            <form method="POST" action="{{ route('admin.withdrawals.act', [$w['id'], 'reject']) }}">@csrf<button class="btn-secondary btn-sm" type="submit">Refuser</button></form>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-500">Aucune demande en attente.</p>
                @endforelse
            </div>
        </section>
        <form method="POST" action="{{ route('admin.accounting.rate') }}" class="card space-y-3 self-start">
            @csrf @method('PUT')
            <h2 class="section-title">Taux de commission</h2>
            <x-input name="commission_rate" type="number" min="8" max="10" step="0.5" label="Taux (%)" :value="$rate" hint="Entre 8 et 10 %. S’applique aux nouveaux paiements." />
            <button class="btn-primary w-full" type="submit">Enregistrer</button>
        </form>
    </div>

    <section class="mt-6" x-data="{ sel: [] }">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="section-title">Commissions par réservation</h2>
            <form method="POST" action="{{ route('admin.accounting.mark-paid') }}" x-show="sel.length" x-cloak>
                @csrf
                <template x-for="id in sel" :key="id"><input type="hidden" name="commission_ids[]" :value="id"></template>
                <button class="btn-primary btn-sm" type="submit">Marquer <span x-text="sel.length"></span> comme reversée(s)</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="w-8 px-4 py-3"><span class="sr-only">Sélection</span></th><th class="px-4 py-3">Réservation</th><th class="px-4 py-3">Établissement</th><th class="px-4 py-3 text-right">Payé</th><th class="px-4 py-3 text-right">Commission</th><th class="px-4 py-3 text-right">Pour l’établissement</th><th class="px-4 py-3">État</th></tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($commissions as $c)
                        <tr>
                            <td class="px-4 py-3">@if ($c['status'] !== 'paid')<input type="checkbox" value="{{ $c['id'] }}" x-model.number="sel" class="h-4 w-4 accent-[rgb(var(--brand))]" aria-label="Sélectionner">@endif</td>
                            <td class="px-4 py-3">@if ($c['booking'] ?? null)<a class="link" href="{{ route('admin.reservations.show', $c['booking']['id']) }}">{{ $c['booking']['booking_number'] ?? '#' . $c['booking']['id'] }}</a>@endif<span class="block text-xs text-gray-500">{{ Fmt::date($c['created_at']) }}</span></td>
                            <td class="px-4 py-3">{{ $c['booking']['accommodation']['name'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($c['booking_amount']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($c['commission_amount']) }} <span class="text-xs text-gray-500">({{ (float) $c['commission_rate'] }} %)</span></td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ Fmt::price($c['host_amount']) }}</td>
                            <td class="px-4 py-3">{{ $c['status'] === 'paid' ? 'Reversée' : ($c['released_at'] ? 'À reverser' : 'Avant arrivée') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune commission pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pager :meta="$meta" />
    </section>
</x-layouts.panel>
