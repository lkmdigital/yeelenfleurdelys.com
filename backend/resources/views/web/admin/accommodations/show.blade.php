@php
    use App\Support\Fmt; use App\Http\Controllers\Web\Admin\AccommodationController as A;
    [$st, $cls] = Fmt::accommodationStatus($a['status']);
    $s = $a['stats'] ?? [];
    $act = fn ($action) => route('admin.accommodations.status', $a['id']);
@endphp
<x-layouts.panel space="admin" :title="$a['name']">
    <a href="{{ route('admin.accommodations') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Établissements</a>
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <h1 class="page-title">{{ $a['name'] }}</h1>
        <span class="badge {{ $cls }}">{{ $st }}</span>
        <a href="{{ route('accommodations.show', $a['id']) }}" target="_blank" class="btn-ghost btn-sm ml-auto"><x-icon name="external-link" /> Page publique</a>
    </div>
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-kpi label="Encaissé (30 j)" :value="Fmt::price($s['amount_collected_30d'] ?? 0)" icon="wallet" :hint="'Total : ' . Fmt::price($s['amount_collected_all_time'] ?? 0)" />
        <x-kpi label="Occupation" :value="round($s['occupancy_rate'] ?? 0) . ' %'" icon="bed-double" />
        <x-kpi label="Chambres libres ce soir" :value="($s['free_rooms_today'] ?? 0) . ' / ' . ($s['total_rooms'] ?? 0)" icon="door-open" />
        <x-kpi label="Réservations confirmées" :value="$s['confirmed_bookings_count'] ?? 0" icon="calendar-check" :href="route('admin.reservations', ['accommodation_id' => $a['id']])" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.accommodations.update', $a['id']) }}" class="card grid gap-4 sm:grid-cols-2">
                @csrf @method('PUT')
                <h2 class="section-title sm:col-span-2">Fiche</h2>
                <x-input name="name" label="Nom" :value="$a['name']" required />
                <x-input name="whatsapp" label="WhatsApp" :value="$a['whatsapp']" />
                <x-input name="address" label="Adresse" :value="$a['address']" required class="sm:col-span-2" />
                <x-input name="city" label="Ville" :value="$a['city']" required />
                <x-input name="price_per_night" type="number" min="0" step="500" label="Prix de base (FCFA)" :value="(int) $a['price_per_night']" required />
                <x-input name="latitude" type="number" step="any" label="Latitude" :value="$a['latitude']" />
                <x-input name="longitude" type="number" step="any" label="Longitude" :value="$a['longitude']" />
                <x-textarea name="description" label="Description" :value="$a['description']" rows="5" required class="sm:col-span-2" />
                <div class="sm:col-span-2"><button class="btn-primary" type="submit">Enregistrer</button></div>
            </form>
            <section class="card">
                <h2 class="section-title">Chambres</h2>
                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($rooms as $r)
                        <li class="flex justify-between gap-3 py-2"><span>{{ $r['name'] }} <span class="text-gray-500">· {{ $r['quantity'] ?? 1 }} ch. · {{ $r['capacity'] }} pers.</span>@unless ($r['is_active'])<span class="badge ml-1 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">Masquée</span>@endunless</span><span class="font-semibold tabular-nums">{{ Fmt::price($r['price_per_night']) }}</span></li>
                    @empty
                        <li class="py-2 text-gray-500">Aucune chambre enregistrée par le gérant.</li>
                    @endforelse
                </ul>
            </section>
        </div>
        <aside class="space-y-4">
            <section class="card text-sm">
                <h2 class="section-title">Gérant</h2>
                @if ($a['host'] ?? null)
                    <p class="mt-2 font-medium">{{ $a['host']['name'] }}</p>
                    <p class="break-all text-gray-600 dark:text-gray-400">{{ $a['host']['email'] }}</p>
                    <a href="{{ route('admin.hosts.show', $a['host']['id']) }}" class="link mt-2 inline-block">Voir le dossier</a>
                @endif
                <p class="mt-3 text-xs text-gray-500">Code : {{ $a['establishment_code'] }} · {{ A::TYPES[$a['type']] ?? $a['type'] }}</p>
            </section>
            <section class="card space-y-3">
                <h2 class="section-title">Visibilité</h2>
                @if ($a['status'] !== 'published')
                    <form method="POST" action="{{ $act('approve') }}">@csrf<input type="hidden" name="action" value="approve"><button class="btn-primary w-full" type="submit">Mettre en ligne</button></form>
                @endif
                @if ($a['status'] === 'published')
                    <form method="POST" action="{{ $act('toggle') }}">@csrf<input type="hidden" name="action" value="toggle-featured"><button class="btn-secondary w-full" type="submit">{{ $a['is_featured'] ? 'Retirer de la mise en avant' : 'Mettre en avant sur l’accueil' }}</button></form>
                    <x-modal-confirm :action="$act('disable')" title="Désactiver cet établissement ?" confirm="Désactiver" button="Désactiver" button-class="btn-secondary w-full" danger>
                        <input type="hidden" name="action" value="disable">Il n’apparaîtra plus sur le site et ne pourra plus recevoir de réservation. Les réservations existantes sont conservées.
                    </x-modal-confirm>
                @elseif ($a['status'] === 'disabled')
                    <form method="POST" action="{{ $act('enable') }}">@csrf<input type="hidden" name="action" value="enable"><button class="btn-secondary w-full" type="submit">Réactiver</button></form>
                @endif
                @if ($a['status'] === 'pending')
                    <form method="POST" action="{{ $act('reject') }}" class="space-y-2">@csrf<input type="hidden" name="action" value="reject"><x-textarea name="reason" label="Motif du refus" rows="2" /><button class="btn-secondary w-full" type="submit">Refuser la fiche</button></form>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.panel>
