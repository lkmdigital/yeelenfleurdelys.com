@php use App\Support\Fmt; use App\Http\Controllers\Web\Host\BookingController as B; @endphp
<x-layouts.panel space="admin" title="Réservations" heading="Réservations (tous établissements)">
    {{-- Mobile : filtres repliés derrière un bouton (ouverts d'office si un filtre est actif). --}}
    <div x-data="{ f: {{ $filters ? 'true' : 'false' }} }">
    <button type="button" class="btn-secondary mb-3 md:hidden" @click="f = !f" :aria-expanded="f"><x-icon name="sliders-horizontal" class="h-4 w-4" /> Filtres @if ($filters)<span class="badge bg-primary text-white">{{ count($filters) }}</span>@endif</button>
    <form method="GET" class="card mb-4 hidden gap-3 sm:grid-cols-2 md:grid lg:grid-cols-3 xl:grid-cols-[1fr_1fr_1fr_1fr_140px_140px_auto]" :class="f && '!grid'">
        <x-input name="search" label="Rechercher" :value="$filters['search'] ?? ''" placeholder="Client, n°…" />
        <x-select name="accommodation_id" label="Établissement" :options="$establishments" placeholder="Tous" :value="$filters['accommodation_id'] ?? ''" />
        <x-select name="status" label="Statut" :options="B::STATUSES" :value="$filters['status'] ?? 'all'" />
        <x-select name="payment_status" label="Paiement" :options="B::PAYMENTS" :value="$filters['payment_status'] ?? 'all'" />
        <x-input name="from_date" type="date" label="Arrivée du" :value="$filters['from_date'] ?? ''" />
        <x-input name="to_date" type="date" label="au" :value="$filters['to_date'] ?? ''" />
        <div class="flex items-end gap-2"><button class="btn-primary" type="submit">Filtrer</button>@if ($filters)<a class="btn-ghost" href="{{ route('admin.reservations') }}">Effacer</a>@endif</div>
    </form>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Réservation</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Séjour</th><th class="px-4 py-3 text-right">Montant</th><th class="px-4 py-3">Statut</th></tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($bookings as $b)
                    <tr>
                        <td class="px-4 py-3"><a class="link" href="{{ route('admin.reservations.show', $b['id']) }}">{{ $b['booking_number'] ?? '#' . $b['id'] }}</a><span class="block text-xs text-gray-500">{{ Fmt::date($b['created_at']) }}</span></td>
                        <td class="px-4 py-3">{{ ($b['traveler_name'] ?? null) ?: ($b['user']['name'] ?? '—') }}<span class="block text-xs text-gray-500">{{ $b['user']['phone'] ?? '' }}</span></td>
                        <td class="px-4 py-3">{{ $b['accommodation']['name'] ?? '—' }}<span class="block text-xs text-gray-500">{{ $b['room']['name'] ?? '' }}</span></td>
                        <td class="whitespace-nowrap px-4 py-3">{{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Fmt::price($b['total_price']) }}<span class="block text-xs text-gray-500">{{ Fmt::paymentStatus($b['payment_status'], $b['display_payment_status_label'] ?? null) }}</span></td>
                        <td class="px-4 py-3"><x-status :status="$b['status']" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucune réservation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
