@props(["title", "intro" => null])
<x-layouts.public :title="$title">
    <div class="mx-auto max-w-4xl px-4 py-12">
        <h1 class="page-title text-3xl">{{ $title }}</h1>
        @if ($intro)<p class="mb-8 mt-2 text-gray-600 dark:text-gray-400">{{ $intro }}</p>@endif
        <x-flash />
        {{ $slot }}
    </div>
</x-layouts.public>
