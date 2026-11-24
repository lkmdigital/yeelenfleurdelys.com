<x-layouts.public :title="$doc['title'] ?? $title">
    <div class="mx-auto max-w-3xl px-4 py-12">
        @if (!$doc)
            <x-empty title="Document pas encore disponible" icon="file-text">Ce document est en cours de préparation et sera publié prochainement.</x-empty>
        @else
            <article>
                <h1 class="page-title text-3xl">{{ $doc['title'] }}</h1>
                <p class="mb-8 mt-2 text-sm text-gray-500">Version {{ $doc['version'] ?? '1' }}@if (!empty($doc['published_at'])) · en vigueur depuis le {{ \App\Support\Fmt::date($doc['published_at'], 'j F Y') }}@endif</p>
                @foreach (preg_split('/\n{2,}/', (string) $doc['content']) as $block)
                    @php $t = trim($block); @endphp
                    @continue($t === '')
                    @if (preg_match('/^TITRE\s+[IVXLCDM]+\s*—/u', $t))
                        <h2 class="mb-2 mt-10 border-b border-gray-200 pb-2 text-sm font-bold uppercase tracking-wide text-gray-500 dark:border-gray-700">{{ $t }}</h2>
                    @elseif (preg_match('/^Article\s+\d+\s*—/u', $t))
                        <h3 class="mb-3 mt-6 text-lg font-bold text-primary">{{ $t }}</h3>
                    @else
                        <p class="mb-4 whitespace-pre-line leading-relaxed text-gray-700 dark:text-gray-300">{{ $t }}</p>
                    @endif
                @endforeach
            </article>
        @endif
    </div>
</x-layouts.public>
