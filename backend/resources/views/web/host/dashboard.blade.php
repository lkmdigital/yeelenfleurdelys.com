@php
    use App\Support\Fmt;
    $s = $stats ?? [];
    $monthly = collect($s['monthly_revenue'] ?? [])->sortBy('month')->values();
    $monthLabel = fn ($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->locale('fr')->isoFormat('MMM YY');
@endphp
<x-layouts.panel space="host" title="Tableau de bord" :heading="'Bonjour ' . (auth()->user()->first_name ?: auth()->user()->name)">
    @if ($readiness && !$readiness['ready'])
        <div class="card mb-6 border-secondary/60 bg-secondary/10">
            <h2 class="section-title">Votre fiche n’est pas encore complète</h2>
            <ul class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                @foreach ($readiness['checks'] as $c)
                    <li class="flex items-start gap-2">
                        <x-icon :name="$c['ok'] ? 'check-circle' : 'circle'" class="mt-0.5 h-4 w-4 shrink-0 {{ $c['ok'] ? 'text-green-600' : 'text-gray-400' }}" />
                        <span class="{{ $c['ok'] ? 'text-gray-500' : '' }}">{{ $c['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('host.property') }}" class="btn-primary mt-4">Compléter ma fiche</a>
        </div>
    @endif

    @if ($stats)
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-kpi label="Séjours à venir" :value="$s['upcoming_bookings'] ?? 0" icon="calendar-check" :href="route('host.reservations', ['status' => 'confirmed'])" />
            <x-kpi label="Revenus du mois" :value="Fmt::price($s['revenue_this_month'] ?? 0)" icon="wallet" :hint="isset($s['revenue_last_month']) ? 'Mois dernier : ' . Fmt::price($s['revenue_last_month']) : null" />
            <x-kpi label="Taux d’occupation" :value="round($s['occupancy_rate'] ?? 0) . ' %'" icon="bed-double" hint="30 derniers jours" />
            <x-kpi label="Chambres libres ce soir" :value="$s['available_rooms_now'] ?? 0" icon="door-open" :href="route('host.calendar')" />
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="section-title">Demandes à confirmer</h2>
                @if ($requestsTotal)<a href="{{ route('host.requests') }}" class="link text-sm">Tout voir ({{ $requestsTotal }})</a>@endif
            </div>
            <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
                @forelse ($requests as $b)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                        <div>
                            <a href="{{ route('host.bookings.show', $b['id']) }}" class="font-semibold hover:underline">{{ ($b['traveler_name'] ?? null) ?: ($b['user']['name'] ?? 'Client') }}</a>
                            <span class="block text-xs text-gray-500">{{ $b['room']['name'] ?? '' }} · {{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }}</span>
                        </div>
                        <form method="POST" action="{{ route('host.bookings.act', [$b['id'], 'approve']) }}">@csrf<button class="btn-primary btn-sm" type="submit">Confirmer</button></form>
                    </div>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-500">Aucune demande en attente.</p>
                @endforelse
            </div>
        </section>
        <section>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="section-title">Arrivées des 7 prochains jours</h2>
                <a href="{{ route('host.reservations') }}" class="link text-sm">Réservations</a>
            </div>
            <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
                @forelse (array_slice($arrivals, 0, 6) as $b)
                    <a href="{{ route('host.bookings.show', $b['id']) }}" class="flex items-center justify-between gap-3 px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <span><span class="block font-semibold">{{ ($b['traveler_name'] ?? null) ?: ($b['user']['name'] ?? 'Client') }}</span><span class="text-xs text-gray-500">{{ $b['room']['name'] ?? '' }} · {{ Fmt::nights($b['check_in'], $b['check_out']) }} nuit(s)</span></span>
                        <span class="whitespace-nowrap text-right font-medium">{{ Fmt::date($b['check_in'], 'd/m') }}<x-status class="mt-1 block" :status="$b['status']" /></span>
                    </a>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-500">Aucune arrivée prévue cette semaine.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
        @if ($stats)
            <section class="card">
                <h2 class="section-title">Revenus par mois</h2>
                @if ($monthly->isNotEmpty())
                    <div class="mt-4 h-64"><canvas x-data="chart({ type: 'bar', labels: @js($monthly->map(fn ($m) => $monthLabel($m['month']))), datasets: [{ label: 'Revenus (FCFA)', data: @js($monthly->map(fn ($m) => (float) $m['revenue'])) }] })" aria-label="Revenus par mois"></canvas></div>
                @else
                    <p class="mt-3 text-sm text-gray-500">Les revenus apparaîtront ici après les premières réservations payées.</p>
                @endif
            </section>
        @endif
        <section class="card">
            <h2 class="section-title">Arrivée d’un client</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Saisissez le code à 8 caractères reçu par le client pour enregistrer son arrivée.</p>
            <form method="POST" action="{{ route('host.checkin') }}" class="mt-4 space-y-3">
                @csrf
                <x-input name="confirmation_code" label="Code d’arrivée" maxlength="8" autocomplete="off" class="uppercase" required />
                <button class="btn-primary w-full" type="submit">Enregistrer l’arrivée</button>
            </form>
        </section>
    </div>
</x-layouts.panel>
