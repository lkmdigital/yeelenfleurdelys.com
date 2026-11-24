@php use App\Http\Controllers\Web\Host\BookingController as B; @endphp
<x-layouts.panel space="host" title="Réservations" heading="Réservations">
    {{-- Mobile : filtres repliés derrière un bouton (ouverts d'office si un filtre est actif). --}}
    <div x-data="{ f: {{ $filters ? 'true' : 'false' }} }">
    <div class="mb-3 flex items-center justify-between gap-2 md:hidden">
        <button type="button" class="btn-secondary" @click="f = !f" :aria-expanded="f"><x-icon name="sliders-horizontal" class="h-4 w-4" /> Filtres @if ($filters)<span class="badge bg-primary text-white">{{ count($filters) }}</span>@endif</button>
        <a href="{{ route('host.reservations.export', $filters) }}" class="btn-ghost"><x-icon name="download" /> CSV</a>
    </div>
    <form method="GET" class="card mb-4 hidden gap-3 sm:grid-cols-2 md:grid lg:grid-cols-[1fr_1fr_1fr_150px_150px_auto]" :class="f && '!grid'">
        <x-input name="search" label="Rechercher" :value="$filters['search'] ?? ''" placeholder="Nom, e-mail, n°…" />
        <x-select name="status" label="Statut" :options="B::STATUSES" :value="$filters['status'] ?? 'all'" />
        <x-select name="payment_status" label="Paiement" :options="B::PAYMENTS" :value="$filters['payment_status'] ?? 'all'" />
        <x-input name="from_date" type="date" label="Arrivée du" :value="$filters['from_date'] ?? ''" />
        <x-input name="to_date" type="date" label="au" :value="$filters['to_date'] ?? ''" />
        <div class="flex items-end gap-2">
            <button class="btn-primary" type="submit">Filtrer</button>
            @if ($filters)<a href="{{ route('host.reservations') }}" class="btn-ghost">Effacer</a>@endif
        </div>
    </form>
    </div>
    <div class="mb-3 hidden justify-end md:flex">
        <a href="{{ route('host.reservations.export', $filters) }}" class="btn-secondary btn-sm"><x-icon name="download" /> Exporter (CSV)</a>
    </div>
    @include('web.host.bookings._table', ['emptyTitle' => $filters ? 'Aucune réservation pour ces filtres' : 'Aucune réservation pour le moment'])
    <x-pager :meta="$meta" />
</x-layouts.panel>
