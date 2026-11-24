@php
    use App\Support\Fmt;
    $m = fn ($k) => \Carbon\Carbon::createFromFormat('Y-m', $k)->locale('fr')->isoFormat('MMM YY');
@endphp
<x-layouts.panel space="admin" title="Vue globale" heading="Vue globale">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-kpi label="Revenus du mois" :value="Fmt::price($s['revenue']['this_month'] ?? 0)" icon="wallet" :hint="'Année : ' . Fmt::price($s['revenue']['this_year'] ?? 0)" :href="route('admin.accounting')" />
        <x-kpi label="Réservations" :value="$s['bookings']['total'] ?? 0" icon="calendar-check" :hint="($s['bookings']['confirmed'] ?? 0) . ' confirmée(s)'" :href="route('admin.reservations')" />
        <x-kpi label="Taux d’occupation" :value="round($s['kpis']['occupancy_rate'] ?? 0) . ' %'" icon="bed-double" hint="30 derniers jours" :href="route('admin.analytics')" />
        <x-kpi label="Clients inscrits" :value="$s['users']['total'] ?? 0" icon="users" :href="route('admin.users')" />
    </div>

    <h2 class="section-title mb-3 mt-8">Établissements ce mois-ci</h2>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($establishments as $e)
            @php [$st, $cls] = Fmt::accommodationStatus($e['status']); @endphp
            <article class="card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('admin.accommodations.show', $e['id']) }}" class="font-semibold hover:underline">{{ $e['name'] }}</a>
                        <p class="text-xs text-gray-500">{{ $e['city'] }} · {{ $e['manager']['name'] ?? 'Sans gérant' }}</p>
                    </div>
                    <span class="badge {{ $cls }}">{{ $st }}</span>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-gray-500">Revenus</dt><dd class="font-semibold tabular-nums">{{ Fmt::price($e['revenue_month']) }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Réservations</dt><dd class="font-semibold">{{ $e['bookings_month'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Arrivées 7 j</dt><dd class="font-semibold">{{ $e['arrivals_7_days'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">En attente</dt><dd class="font-semibold {{ $e['pending_count'] ? 'text-orange-700' : '' }}">{{ $e['pending_count'] }}</dd></div>
                </dl>
                <div class="mt-3 flex gap-2 border-t border-gray-200 pt-3 text-sm dark:border-gray-800">
                    <a href="{{ route('admin.reservations', ['accommodation_id' => $e['id']]) }}" class="link">Réservations</a>
                    <span class="text-gray-300">·</span>
                    <a href="{{ route('accommodations.show', $e['id']) }}" class="link" target="_blank">Page publique</a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px]">
        <section class="card">
            <h2 class="section-title">Revenus sur 12 mois</h2>
            <div class="mt-4 h-64"><canvas x-data="chart({ type: 'bar', labels: @js(array_map(fn ($t) => $m($t['month']), $trend)), datasets: [{ label: 'Revenus (FCFA)', data: @js(array_map(fn ($t) => (float) $t['revenue'], $trend)) }] })"></canvas></div>
        </section>
        <section>
            <div class="mb-3 flex items-center justify-between"><h2 class="section-title">Demandes à confirmer</h2>@if ($pendingTotal)<a href="{{ route('admin.reservations', ['status' => 'awaiting_host_confirmation']) }}" class="link text-sm">Tout voir ({{ $pendingTotal }})</a>@endif</div>
            <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
                @forelse ($pending as $b)
                    <a href="{{ route('admin.reservations.show', $b['id']) }}" class="block px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <span class="font-semibold">{{ $b['user']['name'] ?? 'Client' }}</span> · {{ $b['accommodation']['name'] ?? '' }}
                        <span class="block text-xs text-gray-500">{{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }} · demandée {{ \Carbon\Carbon::parse($b['created_at'])->locale('fr')->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-500">Aucune demande en attente.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.panel>
