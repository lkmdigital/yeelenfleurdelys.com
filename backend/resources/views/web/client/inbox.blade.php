@php use App\Support\Fmt; @endphp
<x-layouts.panel space="client" title="Messages" heading="Messages">
    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Les échanges liés à une réservation se trouvent aussi sur la page de la réservation.</p>
    <div class="space-y-4">
        @forelse ($threads as $t)
            <details class="card" @if ($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-start justify-between gap-3">
                    <span><span class="block font-semibold">{{ $t['subject'] ?? 'Message' }}</span><span class="text-xs text-gray-500">{{ $t['is_from_platform'] ?? false ? 'Yeelen & Fleur de Lys' : ($t['sender']['name'] ?? '') }} · {{ Fmt::datetime($t['created_at']) }}</span></span>
                    @if (empty($t['read_at']) && ($t['recipient_id'] ?? null) === $me)<span class="badge bg-secondary text-onaccent">Nouveau</span>@endif
                </summary>
                <div class="mt-4 space-y-3">
                    @foreach (array_merge([$t], $t['replies'] ?? []) as $m)
                        @php $mine = ($m['sender_id'] ?? null) === $me; @endphp
                        <div class="flex {{ $mine ? 'justify-end' : '' }}">
                            <div class="max-w-[85%] rounded-lg px-3 py-2 text-sm {{ $mine ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-gray-800' }}">
                                <p class="whitespace-pre-line">{{ $m['body'] }}</p>
                                <p class="mt-1 text-[11px] opacity-70">{{ Fmt::datetime($m['created_at']) }}</p>
                            </div>
                        </div>
                    @endforeach
                    @unless ($t['is_from_platform'] ?? false)
                        <form method="POST" action="{{ route('client.inbox.reply') }}" class="flex gap-2">
                            @csrf <input type="hidden" name="parent_id" value="{{ $t['id'] }}">
                            <input name="body" class="input" placeholder="Votre réponse…" maxlength="2000" required aria-label="Votre réponse">
                            <button class="btn-primary" type="submit">Répondre</button>
                        </form>
                    @endunless
                </div>
            </details>
        @empty
            <x-empty title="Aucun message" icon="inbox" />
        @endforelse
    </div>
</x-layouts.panel>
