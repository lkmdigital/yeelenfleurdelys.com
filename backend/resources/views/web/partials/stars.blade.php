<span class="inline-flex items-center gap-0.5 text-secondary" aria-label="{{ $value }} sur 5">
    @for ($s = 1; $s <= 5; $s++)
        <x-icon name="star" class="h-4 w-4 {{ $s <= round($value) ? 'fill-current' : 'text-gray-300 dark:text-gray-600' }}" />
    @endfor
</span>
