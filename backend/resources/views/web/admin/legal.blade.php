@php use App\Support\Fmt; @endphp
<x-layouts.panel space="admin" title="Mentions légales" heading="Mentions légales">
    <nav class="mb-4 flex flex-wrap gap-1 text-sm" aria-label="Documents">
        @foreach ($docs as $d)
            <a href="{{ route('admin.legal', ['doc' => $d['slug']]) }}" class="rounded-lg px-3 py-2 font-medium {{ ($current['slug'] ?? null) === $d['slug'] ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">{{ $d['title'] }}</a>
        @endforeach
    </nav>
    @if ($current)
        <form method="POST" action="{{ route('admin.legal.update', $current['slug']) }}" class="card space-y-4">
            @csrf @method('PUT')
            <x-input name="title" label="Titre" :value="$current['title']" required />
            <x-textarea name="content" label="Texte" :value="$current['content']" rows="22" required hint="Mise en forme possible en HTML simple (titres h2, paragraphes, listes)." class="font-mono" />
            <x-checkbox name="is_published" label="Publié sur le site" :checked="$current['is_published']" />
            <div class="flex flex-wrap items-center gap-3">
                <button class="btn-primary" type="submit">Enregistrer</button>
                <span class="text-xs text-gray-500">Version {{ $current['version'] }} · modifiée le {{ Fmt::datetime($current['updated_at']) }}</span>
                <a class="link ml-auto text-sm" href="{{ url('/' . $current['slug']) }}" target="_blank">Voir sur le site</a>
            </div>
        </form>
    @else
        <x-empty title="Aucun document" icon="scale" />
    @endif
</x-layouts.panel>
