@php
    use App\Support\Fmt;
    $monthly = collect($s['monthly_revenue'] ?? [])->sortBy('month')->values();
    $weeks = collect($s['occupancy_by_week'] ?? []);
    $byRoom = collect($s['revenue_by_room_type'] ?? []);
    $cat = ['single' => 'Simple', 'double' => 'Double', 'twin' => 'Lits jumeaux', 'triple' => 'Triple', 'suite' => 'Suite', 'other' => 'Autre'];
    $k = $s['kpis'] ?? [];
@endphp
<x-layouts.panel space="host" title="Statistiques" heading="Statistiques">
    @if (!$s)
        <x-empty title="Accès réservé" icon="bar-chart-3">Votre compte n’a pas accès aux statistiques.</x-empty>
    @else
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-kpi label="Réservations" :value="$s['total_bookings'] ?? 0" icon="calendar-check" :hint="($s['bookings_this_month'] ?? 0) . ' ce mois-ci'" />
            <x-kpi label="Revenus de l’année" :value="Fmt::price($s['annual_revenue'] ?? 0)" icon="wallet" />
            <x-kpi label="Prix moyen par nuit vendue" :value="Fmt::price($k['average_price_per_room'] ?? 0)" icon="tag" :hint="($k['room_nights_sold'] ?? 0) . ' nuit(s) vendue(s) sur 30 jours'" />
            <x-kpi label="Note moyenne" :value="number_format((float) ($s['average_rating'] ?? 0), 1, ',', ' ') . ' / 5'" icon="star" :href="route('host.reviews')" />
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="card">
                <h2 class="section-title">Revenus par mois</h2>
                @if ($monthly->isNotEmpty())
                    <div class="mt-4 h-64"><canvas x-data="chart({ type: 'line', labels: @js($monthly->map(fn ($m) => \Carbon\Carbon::createFromFormat('Y-m', $m['month'])->locale('fr')->isoFormat('MMM YY'))), datasets: [{ label: 'Revenus (FCFA)', data: @js($monthly->map(fn ($m) => (float) $m['revenue'])) }] })"></canvas></div>
                @else
                    <p class="mt-3 text-sm text-gray-500">Pas encore de revenus.</p>
                @endif
            </section>
            <section class="card">
                <h2 class="section-title">Taux d’occupation par semaine</h2>
                @if ($weeks->isNotEmpty())
                    <div class="mt-4 h-64"><canvas x-data="chart({ type: 'bar', labels: @js($weeks->map(fn ($w) => Fmt::date($w['start_date'], 'd/m'))), datasets: [{ label: 'Occupation (%)', data: @js($weeks->pluck('occupancy_rate')) }], options: { scales: { y: { beginAtZero: true, max: 100 } } } })"></canvas></div>
                @endif
            </section>
            <section class="card">
                <h2 class="section-title">Revenus par type de chambre</h2>
                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($byRoom as $r)
                        <li class="flex justify-between py-2"><span>{{ $cat[$r['room_category']] ?? ($r['room_category'] ?: 'Autre') }}</span><span class="font-semibold tabular-nums">{{ Fmt::price($r['revenue']) }}</span></li>
                    @empty
                        <li class="py-2 text-gray-500">Aucune donnée.</li>
                    @endforelse
                </ul>
            </section>
            <section class="card">
                <h2 class="section-title">Chambres</h2>
                @php $rs = $s['room_stats'] ?? []; @endphp
                <dl class="mt-3 grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Types de chambre actifs</dt><dd class="text-xl font-bold">{{ $rs['active_rooms'] ?? 0 }} / {{ $rs['total_rooms'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Libres ce soir</dt><dd class="text-xl font-bold">{{ $s['available_rooms_now'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Sans photo</dt><dd class="text-xl font-bold">{{ $rs['rooms_needing_images'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Promotions en cours</dt><dd class="text-xl font-bold">{{ $s['promotions']['active_count'] ?? 0 }}</dd></div>
                </dl>
            </section>
        </div>
    @endif
</x-layouts.panel>
