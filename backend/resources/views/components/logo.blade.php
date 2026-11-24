@props(['href' => '/', 'variant' => 'color', 'size' => 'md'])
@php
    // Logo du groupe : le « R » des Résidences Yeelen et la fleur de lys de l'hôtel, en pastilles superposées.
    $disc = ['sm' => 'h-8 w-8', 'md' => 'h-9 w-9 sm:h-10 sm:w-10', 'lg' => 'h-14 w-14'][$size];
    $overlap = ['sm' => '-ml-3', 'md' => '-ml-3 sm:-ml-3.5', 'lg' => '-ml-5'][$size];
    $text = ['sm' => 'text-sm', 'md' => 'text-[15px] sm:text-base', 'lg' => 'text-xl'][$size];
    $white = $variant === 'white';
    // L'anneau prend la couleur du fond pour détacher la fleur de lys du R.
    $ring = $white ? 'ring-primary-dark' : 'ring-white dark:ring-gray-950';
@endphp
<a href="{{ $href }}" class="inline-flex min-w-0 items-center gap-2 sm:gap-2.5" aria-label="Yeelen &amp; Fleur de Lys — accueil">
    <span aria-hidden="true" class="inline-flex shrink-0 items-center">
        <img src="/images/brand/yeelen.png" alt="" width="64" height="64" class="{{ $disc }} rounded-full">
        <img src="/images/brand/fleur-de-lys.png" alt="" width="64" height="64" class="{{ $disc }} {{ $overlap }} relative rounded-full ring-2 {{ $ring }}">
    </span>
    <span class="flex flex-col leading-tight">
        <span class="whitespace-nowrap font-title font-extrabold {{ $text }} {{ $white ? 'text-white' : 'text-gray-900 dark:text-white' }}">Yeelen &amp; Fleur de Lys</span>
        <span class="text-[10px] uppercase tracking-wider {{ $white ? 'text-white/70' : 'text-gray-500 dark:text-gray-400' }}">Hôtel &amp; résidences</span>
    </span>
</a>
