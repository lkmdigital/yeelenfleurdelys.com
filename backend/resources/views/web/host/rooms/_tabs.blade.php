{{-- Onglets d'une chambre existante. --}}
<nav class="mb-5 flex gap-1 overflow-x-auto border-b border-gray-200 text-sm dark:border-gray-800" aria-label="Chambre">
    @foreach ([['host.rooms.edit', 'Informations'], ['host.rooms.images', 'Photos'], ['host.rooms.calendar', 'Disponibilités et tarifs']] as [$r, $l])
        <a href="{{ route($r, $room['id']) }}" @class(['-mb-px whitespace-nowrap border-b-2 px-3 py-2 font-medium', 'border-primary text-primary' => request()->routeIs($r), 'border-transparent text-gray-600 hover:text-gray-900 dark:text-gray-400' => !request()->routeIs($r)])>{{ $l }}</a>
    @endforeach
</nav>
