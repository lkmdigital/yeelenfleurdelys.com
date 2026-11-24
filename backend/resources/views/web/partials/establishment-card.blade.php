@php
    $img = \App\Support\Fmt::coverOrNull($e);
    $price = $e['effective_price_per_night'] ?? $e['price_per_night'] ?? 0;
@endphp
<article class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <a href="{{ route('accommodations.show', $e['id']) }}" class="relative block aspect-[16/10] bg-primary" tabindex="-1" aria-hidden="true">
        <img src="{{ $img ?: \App\Support\DemoPhotos::for($e) }}" alt="" loading="lazy" class="h-full w-full object-cover">
        @unless ($img)
            <span class="absolute bottom-2 right-2 rounded bg-black/50 px-1.5 py-0.5 text-[10px] font-medium text-white/90">Photo d'illustration</span>
        @endunless
        <span class="absolute left-0 top-0 h-full w-1.5 bg-secondary"></span>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $e['name'] }}</h3>
        <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
            <x-icon name="map-pin" class="h-4 w-4 shrink-0 text-primary" /> {{ ($e['address'] ?? null) ?: ($e['city'] ?? '') }}
        </p>
        @if (!empty($e['whatsapp']))
            <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                <x-icon name="phone" class="h-4 w-4 shrink-0 text-primary" /> {{ $e['whatsapp'] }}
            </p>
        @endif
        <div class="mt-auto flex items-end justify-between gap-3 pt-5">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                @if ($price > 0)
                    À partir de <span class="block text-lg font-bold text-gray-900 dark:text-white">{{ \App\Support\Fmt::price($price) }}</span>
                @else
                    Tarifs sur demande
                @endif
            </p>
            <a href="{{ route('accommodations.show', $e['id']) }}" class="btn-primary btn-sm whitespace-nowrap">Voir et réserver</a>
        </div>
    </div>
</article>
