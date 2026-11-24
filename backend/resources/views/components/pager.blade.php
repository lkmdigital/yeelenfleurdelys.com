{{-- Pagination d'une réponse d'API paginée (current_page / last_page / total). --}}
@props(['meta'])
@php
    $cur = (int) ($meta['current_page'] ?? 1);
    $last = (int) ($meta['last_page'] ?? 1);
    $total = $meta['total'] ?? null;
    $url = fn ($p) => request()->fullUrlWithQuery(['page' => $p]);
@endphp
@if ($last > 1)
    <nav class="mt-4 flex items-center justify-between gap-3 text-sm" aria-label="Pagination">
        <p class="text-gray-500">Page {{ $cur }} sur {{ $last }} @if ($total !== null) · {{ $total }} résultat(s) @endif</p>
        <div class="flex gap-2">
            @if ($cur > 1)<a class="btn-secondary btn-sm" href="{{ $url($cur - 1) }}"><x-icon name="chevron-left" /> Précédent</a>@endif
            @if ($cur < $last)<a class="btn-secondary btn-sm" href="{{ $url($cur + 1) }}">Suivant <x-icon name="chevron-right" /></a>@endif
        </div>
    </nav>
@endif
