@php use App\Support\Fmt; $mLabel = fn ($m) => \Carbon\Carbon::create($m['year'], $m['month'], 1)->locale('fr')->isoFormat('MMM YY'); @endphp
<x-layouts.panel space="host" title="Finances" heading="Finances">
    @if ($denied)
        <x-empty title="Accès réservé" icon="wallet">Votre compte n’a pas accès aux finances de l’établissement.</x-empty>
    @else
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-kpi label="Disponible au retrait" :value="Fmt::price($balance)" icon="wallet" hint="Séjours commencés, pas encore reversés" />
            <x-kpi label="En attente d’arrivée" :value="Fmt::price($stats['awaiting_checkin'] ?? 0)" icon="clock" hint="Débloqué à l’arrivée du client" />
            <x-kpi label="Déjà reversé" :value="Fmt::price($stats['paid_revenue'] ?? 0)" icon="check-circle" />
            <x-kpi label="Total encaissé (net)" :value="Fmt::price($stats['total_revenue'] ?? 0)" icon="bar-chart-3" :hint="($stats['total_bookings'] ?? 0) . ' réservation(s)'" />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
            <section class="card">
                <h2 class="section-title">Revenus nets par mois</h2>
                @if ($monthly)
                    <div class="mt-4 h-64"><canvas x-data="chart({ type: 'bar', labels: @js(array_map($mLabel, $monthly)), datasets: [{ label: 'Revenus nets (FCFA)', data: @js(array_map(fn ($m) => (float) $m['total'], $monthly)) }] })"></canvas></div>
                @else
                    <p class="mt-3 text-sm text-gray-500">Aucun paiement encaissé pour le moment.</p>
                @endif
            </section>
            <section class="card">
                <h2 class="section-title">Demander un retrait</h2>
                @if ($isOwner)
                    <form method="POST" action="{{ route('host.finances.withdraw') }}" class="mt-3 space-y-3">
                        @csrf
                        <x-input name="amount" type="number" min="1000" step="500" label="Montant (FCFA)" :value="$balance >= 1000 ? (int) $balance : ''" required />
                        <x-select name="payment_method" label="Recevoir par" :options="['bank_transfer' => 'Virement bancaire', 'mobile_money' => 'Mobile Money']" />
                        <x-textarea name="host_note" label="Note (facultatif)" rows="2" />
                        <button class="btn-primary w-full" type="submit" @disabled($balance < 1000)>Envoyer la demande</button>
                        @if ($balance < 1000)<p class="hint">Le retrait est possible à partir de 1 000 FCFA disponibles.</p>@endif
                    </form>
                    <p class="mt-3 text-xs text-gray-500">Le virement se fait sur le compte bancaire renseigné dans <a class="link" href="{{ route('host.settings') }}">Paramètres</a>.</p>
                @else
                    <p class="mt-2 text-sm text-gray-500">Les demandes de retrait sont réservées au propriétaire du compte.</p>
                @endif
            </section>
        </div>

        @if ($withdrawals)
            <section class="mt-6">
                <h2 class="section-title mb-3">Mes demandes de retrait</h2>
                <div class="table-wrap">
                    <table class="table">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Montant</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Note</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($withdrawals as $w)
                                <tr>
                                    <td class="px-4 py-3">{{ Fmt::date($w['created_at']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($w['amount']) }}</td>
                                    <td class="px-4 py-3">{{ ['pending' => 'En cours de traitement', 'approved' => 'Acceptée', 'paid' => 'Versée', 'rejected' => 'Refusée', 'cancelled' => 'Annulée'][$w['status']] ?? $w['status'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $w['admin_note'] ?? ($w['host_note'] ?? '') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="mt-6">
            <h2 class="section-title mb-3">Détail par réservation</h2>
            @if ($revenues)
                <div class="table-wrap">
                    <table class="table">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Réservation</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Payé par le client</th><th class="px-4 py-3 text-right">Commission</th><th class="px-4 py-3 text-right">Net pour vous</th><th class="px-4 py-3">État</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($revenues as $c)
                                <tr>
                                    <td class="px-4 py-3">@if ($c['booking'] ?? null)<a class="link" href="{{ route('host.bookings.show', $c['booking']['id']) }}">{{ $c['booking']['booking_number'] ?? '#' . $c['booking']['id'] }}</a>@else — @endif</td>
                                    <td class="px-4 py-3">{{ Fmt::date($c['created_at']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($c['booking_amount']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-gray-500">{{ Fmt::price($c['commission_amount']) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ Fmt::price($c['host_amount']) }}</td>
                                    <td class="px-4 py-3">{{ $c['status'] === 'paid' ? 'Reversé' : ($c['released_at'] ? 'Disponible' : 'En attente d’arrivée') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-pager :meta="$meta" />
            @else
                <x-empty title="Aucun paiement encaissé" icon="wallet" />
            @endif
        </section>
    @endif
</x-layouts.panel>
