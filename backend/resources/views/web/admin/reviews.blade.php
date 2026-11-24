@php use App\Support\Fmt; $labels = ['pending' => 'À modérer', 'approved' => 'Publiés', 'hidden' => 'Masqués']; @endphp
<x-layouts.panel space="admin" title="Avis clients" heading="Avis clients">
    <nav class="mb-4 flex flex-wrap gap-1 text-sm" aria-label="Filtrer les avis">
        @foreach (['' => 'Tous'] + $labels as $k => $l)
            <a href="{{ route('admin.reviews', array_filter(['moderation_status' => $k])) }}" class="rounded-lg px-3 py-2 font-medium {{ ($status ?? '') === $k ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">{{ $l }}</a>
        @endforeach
    </nav>
    <div class="space-y-4">
        @forelse ($reviews as $r)
            <article class="card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ $r['user']['name'] ?? 'Client' }} <span class="font-normal text-gray-500">· {{ $r['accommodation']['name'] ?? '' }}</span></p>
                        <p class="text-xs text-gray-500">{{ Fmt::date($r['created_at']) }} · {{ $labels[$r['moderation_status'] ?? 'pending'] ?? $r['moderation_status'] }}</p>
                    </div>
                    @include('web.partials.stars', ['value' => $r['rating']])
                </div>
                <p class="mt-3 whitespace-pre-line text-sm">{{ $r['comment'] }}</p>
                @if ($r['host_reply'] ?? null)<p class="mt-2 border-l-4 border-primary pl-3 text-sm text-gray-600 dark:text-gray-400">Réponse de l’établissement : {{ $r['host_reply'] }}</p>@endif
                <div class="mt-3 flex gap-2">
                    @if (($r['moderation_status'] ?? null) !== 'approved')<form method="POST" action="{{ route('admin.reviews.act', [$r['id'], 'approve']) }}">@csrf<button class="btn-primary btn-sm" type="submit">Publier</button></form>@endif
                    @if (($r['moderation_status'] ?? null) !== 'hidden')<form method="POST" action="{{ route('admin.reviews.act', [$r['id'], 'hide']) }}">@csrf<button class="btn-secondary btn-sm" type="submit">Masquer</button></form>@endif
                </div>
            </article>
        @empty
            <x-empty title="Aucun avis" icon="star" />
        @endforelse
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
