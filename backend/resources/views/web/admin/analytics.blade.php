@php
    use App\Support\Fmt;
    $m = fn ($k) => \Carbon\Carbon::createFromFormat('Y-m', $k)->locale('fr')->isoFormat('MMM YY');
    $k = $s['kpis'] ?? [];
@endphp
<x-layouts.panel space="admin" title="Statistiques" heading="Statistiques">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-kpi label="Revenus encaissés" :value="Fmt::price($a['stats']['total_revenue'] ?? 0)" icon="wallet" />
        <x-kpi label="RevPAR (30 j)" :value="Fmt::price($k['revpar'] ?? 0)" icon="trending-up" hint="Revenu par chambre disponible" />
        <x-kpi label="Prix moyen par nuit" :value="Fmt::price($k['average_price_per_room'] ?? 0)" icon="tag" />
        <x-kpi label="Nuits vendues (30 j)" :value="($k['room_nights_sold'] ?? 0) . ' / ' . ($k['room_nights_available'] ?? 0)" icon="bed-double" />
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card">
            <h2 class="section-title">Revenus et commissions</h2>
            <div class="mt-4 h-64"><canvas x-data="chart({ type: 'line', labels: @js(array_map(fn ($t) => $m($t['month']), $trend)), datasets: [{ label: 'Revenus', data: @js(array_map(fn ($t) => (float) $t['revenue'], $trend)) }, { label: 'Commissions', data: @js(array_map(fn ($t) => (float) $t['commissions'], $trend)) }] })"></canvas></div>
        </section>
        <section class="card">
            <h2 class="section-title">Taux d’occupation (%)</h2>
            <div class="mt-4 h-64"><canvas x-data="chart({ type: 'bar', labels: @js(array_map(fn ($t) => $m($t['month']), $occupancy)), datasets: [{ label: 'Occupation', data: @js(array_column($occupancy, 'occupancy_rate')) }], options: { scales: { y: { beginAtZero: true, max: 100 } } } })"></canvas></div>
        </section>
    </div>
    <section class="mt-6">
        <h2 class="section-title mb-3">Classement des établissements</h2>
        <div class="table-wrap">
            <table class="table">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Ville</th><th class="px-4 py-3 text-right">Réservations</th><th class="px-4 py-3 text-right">Revenus</th></tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($top as $t)
                        <tr><td class="px-4 py-3"><a class="link" href="{{ route('admin.accommodations.show', $t['id']) }}">{{ $t['name'] }}</a></td><td class="px-4 py-3">{{ $t['city'] }}</td><td class="px-4 py-3 text-right tabular-nums">{{ $t['bookings_count'] }}</td><td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($t['revenue']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.panel>
