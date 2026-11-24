@php use App\Support\Fmt; @endphp
<x-layouts.panel space="host" title="Avis" heading="Avis des clients">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600 dark:text-gray-400">Note moyenne : <span class="font-semibold text-gray-900 dark:text-white">{{ number_format((float) ($acc['rating'] ?? 0), 1, ',', ' ') }} / 5</span> · {{ $acc['total_reviews'] ?? 0 }} avis publiés</p>
        <nav class="flex gap-1 text-sm" aria-label="Filtrer les avis">
            @foreach ([null => 'Tous', 'not_replied' => 'Sans réponse', 'replied' => 'Répondus'] as $k => $l)
                <a href="{{ route('host.reviews', array_filter(['reply_status' => $k])) }}" class="rounded-lg px-3 py-2 font-medium {{ $reply === ($k ?: null) ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">{{ $l }}</a>
            @endforeach
        </nav>
    </div>
    <div class="space-y-4">
        @forelse ($reviews as $r)
            <article class="card">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $r['user']['name'] ?? 'Client' }}</p>
                        <p class="text-xs text-gray-500">{{ Fmt::date($r['created_at']) }}@if (($r['moderation_status'] ?? 'approved') !== 'approved') · <span class="text-orange-700">en attente de validation</span>@endif</p>
                    </div>
                    @include('web.partials.stars', ['value' => $r['rating']])
                </div>
                <p class="mt-3 whitespace-pre-line text-sm">{{ $r['comment'] }}</p>
                @if ($r['host_reply'])
                    <div class="mt-3 rounded-lg border-l-4 border-primary bg-gray-50 p-3 text-sm dark:bg-gray-800/50">
                        <p class="font-medium">Votre réponse <span class="font-normal text-gray-500">· {{ Fmt::date($r['host_replied_at']) }}</span></p>
                        <p class="mt-1 whitespace-pre-line">{{ $r['host_reply'] }}</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('host.reviews.reply', $r['id']) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="host_reply" rows="2" class="input" maxlength="2000" required placeholder="Remerciez le client ou apportez une précision…" aria-label="Votre réponse"></textarea>
                        <button class="btn-primary btn-sm" type="submit">Publier ma réponse</button>
                    </form>
                @endif
            </article>
        @empty
            <x-empty title="Aucun avis" icon="star">Après leur séjour, les clients reçoivent un lien pour donner leur avis.</x-empty>
        @endforelse
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
