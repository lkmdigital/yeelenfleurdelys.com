@php use App\Support\Fmt; $next = $stats['upcoming_bookings'][0] ?? null; @endphp
<x-layouts.panel space="client" title="Mon espace" :heading="'Bonjour ' . (auth()->user()->first_name ?: auth()->user()->name)">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-kpi label="Réservations" :value="$stats['total_bookings'] ?? 0" icon="calendar-check" :href="route('client.reservations')" />
        <x-kpi label="Séjours à venir" :value="count($stats['upcoming_bookings'] ?? [])" icon="map-pin" :href="route('client.trips')" />
        <x-kpi label="Points fidélité" :value="number_format($loyalty['points_balance'] ?? 0, 0, ',', ' ')" icon="award" :hint="$loyalty['tier']['label'] ?? null" :href="route('client.programme')" />
        <x-kpi label="Avoirs disponibles" :value="Fmt::price($credit)" icon="wallet" :href="route('client.credits')" />
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
        <section>
            <h2 class="section-title mb-3">Prochain séjour</h2>
            @if ($next)
                @include('web.client._booking-row', ['b' => $next])
            @else
                <x-empty title="Aucun séjour prévu" icon="calendar">
                    <x-slot:action><a href="{{ route('home') }}#etablissements" class="btn-primary">Réserver un séjour</a></x-slot:action>
                </x-empty>
            @endif
        </section>
        <section>
            <div class="mb-3 flex items-center justify-between"><h2 class="section-title">Notifications</h2>@if ($unread)<span class="badge bg-secondary text-onaccent">{{ $unread }} non lue(s)</span>@endif</div>
            <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
                @forelse ($notifications as $n)
                    <p class="px-4 py-3 text-sm {{ $n['read_at'] ? 'text-gray-500' : 'font-medium' }}">{{ $n['message'] }}<span class="mt-0.5 block text-xs font-normal text-gray-500">{{ Fmt::datetime($n['created_at']) }}</span></p>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-500">Aucune notification.</p>
                @endforelse
            </div>
            <a href="{{ route('client.notifications') }}" class="link mt-2 inline-block text-sm">Tout voir</a>
        </section>
    </div>
</x-layouts.panel>
