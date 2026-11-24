{{-- Carte chambre (page établissement) : on choisit d'abord la chambre, les dates viennent ensuite. --}}
@php
    use App\Support\Fmt;
    $img = Fmt::coverOrNull($room);
    $price = $room['effective_price_per_night'] ?? $room['price_per_night'];
    $roomUrl = route('rooms.show', array_filter(['id' => $room['id'], 'check_in' => $search['check_in'] ?? null, 'check_out' => $search['check_out'] ?? null, 'guests' => $search['guests'] ?? null]));
@endphp
<article class="flex flex-col gap-4 py-5 first:pt-0 last:pb-0 sm:flex-row">
    <a href="{{ $roomUrl }}" class="relative block shrink-0 overflow-hidden rounded-lg bg-primary sm:w-56">
        @if ($img)
            <img src="{{ $img }}" alt="{{ $room['name'] }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
        @elseif (isset($acc))
            <img src="{{ \App\Support\DemoPhotos::room($acc, $room, 600)[0] }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover">
            <span class="absolute bottom-1.5 right-1.5 rounded bg-black/50 px-1.5 py-0.5 text-[10px] font-medium text-white/90">Photo d'illustration</span>
        @else
            <span class="flex aspect-[4/3] items-center justify-center text-white/80"><x-icon name="bed-double" class="h-8 w-8" /></span>
        @endif
    </a>
    <div class="flex min-w-0 flex-1 flex-col">
        <h3 class="text-lg font-bold">
            <a href="{{ $roomUrl }}" class="hover:text-primary">{{ $room['name'] }}</a>
        </h3>
        <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
            <span class="inline-flex items-center gap-1"><x-icon name="users" class="h-4 w-4" /> {{ $room['capacity'] }} pers. max.</span>
            @if (!empty($room['surface_area']))<span class="inline-flex items-center gap-1"><x-icon name="maximize" class="h-4 w-4" /> {{ $room['surface_area'] }} m²</span>@endif
            @if (($room['quantity'] ?? 1) > 1)<span class="inline-flex items-center gap-1"><x-icon name="door-open" class="h-4 w-4" /> {{ $room['quantity'] }} chambres de ce type</span>@endif
        </p>
        @if (!empty($room['description']))<p class="mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-300">{{ $room['description'] }}</p>@endif
        @if (!empty($room['amenities']))
            <ul class="mt-2 flex flex-wrap gap-1.5">
                @foreach (array_slice($room['amenities'], 0, 6) as $a)
                    <li class="badge bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ Fmt::amenity($a) }}</li>
                @endforeach
            </ul>
        @endif
        <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
            <p>
                @if ($price > 0)
                    <span class="text-xl font-bold">{{ Fmt::price($price) }}</span> <span class="text-sm text-gray-500">/ nuit</span>
                @else
                    <span class="text-sm text-gray-600">Tarif sur demande</span>
                @endif
            </p>
            @if ($price > 0)
                <a href="{{ $roomUrl }}" class="btn-primary">Choisir <x-icon name="arrow-right" class="h-4 w-4" /></a>
            @endif
        </div>
    </div>
</article>
