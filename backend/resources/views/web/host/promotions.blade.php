@php use App\Support\Fmt; $today = now()->toDateString(); @endphp
<x-layouts.panel space="host" title="Promotions" heading="Promotions">
    <div class="grid gap-6 xl:grid-cols-[1fr_400px]">
        <section class="space-y-4">
            @forelse ($promotions as $p)
                @php
                    $live = $p['is_active'] && substr($p['start_date'], 0, 10) <= $today && substr($p['end_date'], 0, 10) >= $today;
                    $value = ($p['discount_type'] ?? 'percent') === 'fixed' ? '-' . Fmt::price($p['discount_amount']) . ' / nuit' : '-' . rtrim(rtrim((string) $p['discount_percent'], '0'), '.') . ' %';
                @endphp
                <article class="card" x-data="{ edit: false }">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xl font-bold text-primary">{{ $value }}</p>
                            <p class="text-sm">{{ $p['description'] ?: 'Promotion' }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ Fmt::date($p['start_date']) }} → {{ Fmt::date($p['end_date']) }} · {{ $rooms[$p['room_id']] ?? 'Toutes les chambres' }}@if ($p['min_stay_nights']) · {{ $p['min_stay_nights'] }} nuit(s) min.@endif @if ($p['promo_code']) · code <span class="font-mono font-semibold">{{ $p['promo_code'] }}</span>@endif</p>
                        </div>
                        <span class="badge {{ $live ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ $live ? 'En cours' : (!$p['is_active'] ? 'Désactivée' : (substr($p['end_date'], 0, 10) < $today ? 'Terminée' : 'À venir')) }}</span>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('host.promotions.toggle', $p['id']) }}">@csrf<button class="btn-secondary btn-sm" type="submit">{{ $p['is_active'] ? 'Désactiver' : 'Activer' }}</button></form>
                        <button type="button" class="btn-ghost btn-sm" @click="edit = !edit"><x-icon name="pencil" /> Modifier</button>
                        <x-modal-confirm :action="route('host.promotions.destroy', $p['id'])" method="DELETE" title="Supprimer cette promotion ?" confirm="Supprimer" button="Supprimer" button-class="btn-ghost btn-sm text-red-700" danger>Les réservations déjà faites gardent leur réduction.</x-modal-confirm>
                    </div>
                    <form x-show="edit" x-cloak method="POST" action="{{ route('host.promotions.update', $p['id']) }}" class="mt-4 space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
                        @csrf @method('PUT')
                        @include('web.host._promo-fields', ['p' => $p])
                        <button class="btn-primary btn-sm" type="submit">Enregistrer</button>
                    </form>
                </article>
            @empty
                <x-empty title="Aucune promotion" icon="gift">Créez une réduction pour remplir les périodes creuses.</x-empty>
            @endforelse
        </section>
        <form method="POST" action="{{ route('host.promotions.store') }}" class="card space-y-4 self-start">
            @csrf
            <h2 class="section-title">Nouvelle promotion</h2>
            @include('web.host._promo-fields', ['p' => null])
            <button class="btn-primary w-full" type="submit">Créer la promotion</button>
        </form>
    </div>
</x-layouts.panel>
