@props(['title', 'icon' => 'inbox'])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center dark:border-gray-700']) }}>
    <x-icon :name="$icon" class="mx-auto h-8 w-8 text-gray-400" />
    <p class="mt-3 font-semibold text-gray-900 dark:text-white">{{ $title }}</p>
    @if (trim($slot))<div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $slot }}</div>@endif
    @isset($action)<div class="mt-4">{{ $action }}</div>@endisset
</div>
