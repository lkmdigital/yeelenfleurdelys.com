@php $tabs = ['' => 'Toutes', 'in_progress' => 'En cours', 'finished' => 'Terminées', 'cancelled' => 'Annulées']; @endphp
<x-layouts.panel space="client" title="Mes réservations" heading="Mes réservations">
    <nav class="mb-5 flex flex-wrap gap-2" aria-label="Filtrer">
        @foreach ($tabs as $k => $t)
            <a href="{{ route('client.reservations', array_filter(['status' => $k])) }}" class="rounded-lg px-3 py-2 text-sm {{ (string) $status === (string) $k ? 'bg-primary text-white' : 'border border-gray-300 hover:border-primary dark:border-gray-700' }}">{{ $t }}</a>
        @endforeach
    </nav>
    <div class="space-y-3">
        @forelse ($bookings as $b)
            @include('web.client._booking-row', ['b' => $b])
        @empty
            <x-empty title="Aucune réservation" icon="calendar-check">
                <x-slot:action><a href="{{ route('home') }}#etablissements" class="btn-primary">Réserver un séjour</a></x-slot:action>
            </x-empty>
        @endforelse
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
