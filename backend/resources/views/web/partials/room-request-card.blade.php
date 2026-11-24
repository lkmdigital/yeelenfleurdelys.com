{{-- Chambre sans tarif en ligne : photo, capacité, équipements et demande de tarif (pas de réservation). --}}
<article class="flex flex-col gap-4 py-5 first:pt-0 last:pb-0 sm:flex-row">
    <div class="relative shrink-0 overflow-hidden rounded-lg bg-primary sm:w-56">
        <img src="{{ \App\Support\DemoPhotos::room($acc, $room, 600)[0] }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover">
        <span class="absolute bottom-1.5 right-1.5 rounded bg-black/50 px-1.5 py-0.5 text-[10px] font-medium text-white/90">Photo d'illustration</span>
    </div>
    <div class="flex min-w-0 flex-1 flex-col">
        <h3 class="text-lg font-bold">{{ $room['name'] }}</h3>
        <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
            <span class="inline-flex items-center gap-1"><x-icon name="users" class="h-4 w-4" /> {{ $room['capacity'] }} pers. max.</span>
            @if (!empty($room['surface_area']))<span class="inline-flex items-center gap-1"><x-icon name="maximize" class="h-4 w-4" /> {{ $room['surface_area'] }} m²</span>@endif
        </p>
        @if (!empty($room['amenities']))
            <ul class="mt-2 flex flex-wrap gap-1.5">
                @foreach (array_slice($room['amenities'], 0, 6) as $a)
                    <li class="badge bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ \App\Support\Fmt::amenity($a) }}</li>
                @endforeach
            </ul>
        @endif
        <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Tarif sur demande</p>
            @if ($wa)
                <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Bonjour, je souhaite connaître le tarif et les disponibilités : ' . $room['name'] . ' — ' . $acc['name'] . '.') }}"
                   target="_blank" rel="noopener noreferrer" class="btn-primary"><x-icon name="message-circle" /> Demander le tarif</a>
            @else
                <a href="{{ route('contact') }}" class="btn-primary"><x-icon name="mail" /> Demander le tarif</a>
            @endif
        </div>
    </div>
</article>
