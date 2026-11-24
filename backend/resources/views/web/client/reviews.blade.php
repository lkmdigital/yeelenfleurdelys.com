@php use App\Support\Fmt; @endphp
<x-layouts.panel space="client" title="Mes avis" heading="Mes avis">
    @if ($pending)
        <h2 class="section-title mb-3">À donner</h2>
        <div class="space-y-4">
            @foreach ($pending as $p)
                <form method="POST" action="{{ route('client.reviews.store') }}" class="card space-y-3" x-data="{ rating: 0 }" @if ($focus === $p['booking_id']) id="avis" @endif>
                    @csrf
                    <input type="hidden" name="accommodation_id" value="{{ $p['accommodation']['id'] }}">
                    <input type="hidden" name="booking_id" value="{{ $p['booking_id'] }}">
                    <p class="font-semibold">{{ $p['accommodation']['name'] }} <span class="text-sm font-normal text-gray-500">· séjour terminé le {{ Fmt::date($p['check_out']) }}</span></p>
                    <div class="flex gap-1" role="radiogroup" aria-label="Note">
                        @for ($s = 1; $s <= 5; $s++)
                            <label class="cursor-pointer"><input type="radio" name="rating" value="{{ $s }}" class="sr-only" x-model.number="rating"><x-icon name="star" class="h-7 w-7" x-bind:class="rating >= {{ $s }} ? 'fill-current text-secondary' : 'text-gray-300'" /><span class="sr-only">{{ $s }} sur 5</span></label>
                        @endfor
                    </div>
                    <textarea name="comment" rows="3" class="input" maxlength="1000" required placeholder="Votre avis sur le séjour…" aria-label="Commentaire"></textarea>
                    <button class="btn-primary" type="submit">Publier mon avis</button>
                </form>
            @endforeach
        </div>
    @endif
    <h2 class="section-title mb-3 {{ $pending ? 'mt-8' : '' }}">Avis publiés</h2>
    @forelse ($submitted as $r)
        <article class="card mb-3">
            <div class="flex flex-wrap items-center justify-between gap-2"><p class="font-semibold">{{ $r['accommodation']['name'] ?? '' }}</p><p class="text-xs text-gray-500">{{ Fmt::date($r['created_at']) }}</p></div>
            <div class="mt-1">@include('web.partials.stars', ['value' => (float) $r['rating']])</div>
            <p class="mt-2 text-sm">{{ $r['comment'] }}</p>
            @if ($r['host_reply'])<p class="mt-2 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800"><strong>Réponse de l'établissement :</strong> {{ $r['host_reply'] }}</p>@endif
        </article>
    @empty
        <p class="text-sm text-gray-500">Vous n'avez pas encore publié d'avis.</p>
    @endforelse
</x-layouts.panel>
