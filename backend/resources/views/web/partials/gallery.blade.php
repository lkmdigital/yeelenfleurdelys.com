{{-- Galerie : grande photo + vignettes, agrandissement au clic.
     $demo (facultatif) : photos d'illustration affichées s'il n'y a aucune vraie photo. --}}
@php
    $urls = collect($images ?? [])->sortByDesc(fn ($i) => (int) ($i['is_primary'] ?? 0))->map(fn ($i) => \App\Support\Fmt::img($i['url'] ?? null))->values()->all();
    $isDemo = !$urls && !empty($demo);
    if ($isDemo) $urls = array_values($demo);
@endphp
@if ($urls)
    <div x-data="{ i: 0, open: false, urls: @js($urls) }" @keydown.escape.window="open = false" @keydown.arrow-right.window="if (open) i = (i + 1) % urls.length" @keydown.arrow-left.window="if (open) i = (i - 1 + urls.length) % urls.length">
        <button type="button" class="relative block w-full overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800" @click="open = true" aria-label="Agrandir la photo">
            <img :src="urls[i]" src="{{ $urls[0] }}" alt="{{ $alt ?? '' }}" class="aspect-[16/9] w-full object-cover lg:aspect-[21/9]">
            @if ($isDemo)
                <span class="absolute bottom-2 right-2 rounded bg-black/50 px-2 py-0.5 text-xs font-medium text-white/90">Photos d'illustration</span>
            @endif
        </button>
        @if (count($urls) > 1)
            <div class="mt-2 flex gap-2 overflow-x-auto pb-1">
                @foreach ($urls as $k => $u)
                    <button type="button" @click="i = {{ $k }}" :class="i === {{ $k }} ? 'ring-2 ring-primary' : 'opacity-80 hover:opacity-100'" class="h-16 w-24 shrink-0 overflow-hidden rounded-lg" aria-label="Photo {{ $k + 1 }}">
                        <img src="{{ $u }}" alt="" loading="lazy" class="h-full w-full object-cover">
                    </button>
                @endforeach
            </div>
        @endif
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4" @click.self="open = false" role="dialog" aria-modal="true" aria-label="Photos">
            <button type="button" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" @click="open = false" aria-label="Fermer"><x-icon name="x" class="h-6 w-6" /></button>
            <button type="button" x-show="urls.length > 1" class="absolute left-3 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" @click="i = (i - 1 + urls.length) % urls.length" aria-label="Photo précédente"><x-icon name="chevron-left" class="h-6 w-6" /></button>
            <img :src="urls[i]" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            <button type="button" x-show="urls.length > 1" class="absolute right-3 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" @click="i = (i + 1) % urls.length" aria-label="Photo suivante"><x-icon name="chevron-right" class="h-6 w-6" /></button>
            <p class="absolute bottom-4 text-sm text-white/80"><span x-text="i + 1"></span> / {{ count($urls) }}</p>
        </div>
    </div>
@else
    <div class="flex aspect-[16/9] w-full items-center justify-center rounded-xl bg-primary text-white/80">
        <x-icon name="image" class="h-12 w-12" />
    </div>
@endif
