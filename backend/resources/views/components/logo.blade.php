@props(['href' => '/', 'variant' => 'color', 'size' => 'md'])
@php
    $box = ['sm' => 'h-8 w-8 text-xs', 'md' => 'h-10 w-10 text-sm', 'lg' => 'h-14 w-14 text-base'][$size];
    $text = ['sm' => 'text-sm', 'md' => 'text-base', 'lg' => 'text-xl'][$size];
    $white = $variant === 'white';
@endphp
<a href="{{ $href }}" class="inline-flex items-center gap-2.5" aria-label="Yeelen &amp; Fleur de Lys — accueil">
    <span aria-hidden="true" class="{{ $box }} inline-flex shrink-0 items-center justify-center rounded-lg bg-secondary font-title font-extrabold text-onaccent">YF</span>
    <span class="flex flex-col leading-tight">
        <span class="font-title font-extrabold {{ $text }} {{ $white ? 'text-white' : 'text-gray-900 dark:text-white' }}">Yeelen &amp; Fleur de Lys</span>
        <span class="text-[10px] uppercase tracking-wider {{ $white ? 'text-white/70' : 'text-gray-500 dark:text-gray-400' }}">Hôtel &amp; résidences</span>
    </span>
</a>
