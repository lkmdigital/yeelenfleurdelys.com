@props(['label', 'value', 'icon' => null, 'hint' => null, 'href' => null])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card block p-4 sm:p-5' . ($href ? ' transition hover:border-primary/50' : '')]) }}>
    @if ($icon)<x-icon :name="$icon" class="mb-2 h-5 w-5 text-primary sm:mb-3" />@endif
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold tabular-nums sm:text-2xl text-gray-900 dark:text-white">{{ $value }}</p>
    @if ($hint)<p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>@endif
</{{ $tag }}>
