@props(['title', 'subtitle' => null, 'pageTitle' => null])
<x-layouts.public :title="$pageTitle ?? $title">
    <div class="mx-auto flex max-w-md flex-col px-4 py-10 sm:py-16">
        <div class="card p-6 sm:p-8">
            <h1 class="page-title text-2xl">{{ $title }}</h1>
            @if ($subtitle)<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>@endif
            <div class="mt-6"><x-flash />{{ $slot }}</div>
        </div>
        @isset($after)<div class="mt-5 text-center text-sm text-gray-600 dark:text-gray-400">{{ $after }}</div>@endisset
    </div>
</x-layouts.public>
