@php
    use App\Support\Fmt; use App\Http\Controllers\Web\Host\RoomController as R;
    $r = $room ?? [];
    $current = array_map('mb_strtolower', old('amenities', $r['amenities'] ?? []));
    $keys = array_values(array_unique(array_merge(R::AMENITIES, $current)));
@endphp
<x-layouts.panel space="host" :title="$room ? $room['name'] : 'Nouvelle chambre'">
    <a href="{{ route('host.rooms') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Chambres</a>
    <h1 class="page-title mb-4">{{ $room ? $room['name'] : 'Nouvelle chambre' }}</h1>
    @if ($room) @include('web.host.rooms._tabs') @endif

    <form method="POST" action="{{ $room ? route('host.rooms.update', $room['id']) : route('host.rooms.store') }}" class="max-w-3xl space-y-6">
        @csrf @if ($room) @method('PUT') @endif
        <section class="card grid gap-4 sm:grid-cols-2">
            <x-input name="name" label="Nom de la chambre" :value="$r['name'] ?? ''" hint="Exemple : Chambre double vue piscine." required />
            <x-select name="room_category" label="Catégorie" :options="R::CATEGORIES" :value="$r['room_category'] ?? ($r['type'] ?? 'double')" required />
            <x-input name="price_per_night" type="number" min="0" step="500" label="Prix par nuit (FCFA)" :value="isset($r['price_per_night']) ? (int) $r['price_per_night'] : ''" required />
            <x-input name="single_occupancy_price" type="number" min="0" step="500" label="Prix pour 1 personne (FCFA)" :value="isset($r['single_occupancy_price']) ? (int) $r['single_occupancy_price'] : ''" hint="Facultatif." />
            <x-input name="capacity" type="number" min="1" max="20" label="Personnes maximum" :value="$r['capacity'] ?? 2" required />
            <x-input name="quantity" type="number" min="1" max="100" label="Nombre de chambres identiques" :value="$r['quantity'] ?? 1" required />
            <x-input name="bedrooms" type="number" min="1" max="10" label="Chambres à coucher" :value="$r['bedrooms'] ?? 1" required />
            <x-input name="bathrooms" type="number" min="1" max="10" label="Salles de bain" :value="$r['bathrooms'] ?? 1" required />
            <x-input name="surface_area" type="number" min="0" label="Surface (m²)" :value="$r['surface_area'] ?? ''" />
            <x-textarea name="description" label="Description" :value="$r['description'] ?? ''" rows="4" class="sm:col-span-2" />
            <fieldset class="sm:col-span-2">
                <legend class="label">Équipements</legend>
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach ($keys as $k)
                        <label class="flex items-center gap-2.5 text-sm"><input type="checkbox" name="amenities[]" value="{{ $k }}" @checked(in_array($k, $current, true)) class="h-4 w-4 rounded border-gray-300 accent-[rgb(var(--brand))]"> {{ Fmt::amenity($k) }}</label>
                    @endforeach
                </div>
            </fieldset>
            @if ($room)
                <x-checkbox name="is_active" label="Chambre visible et réservable sur le site" :checked="$r['is_active'] ?? true" class="sm:col-span-2" />
            @endif
        </section>
        <div class="flex flex-wrap items-center gap-3">
            <button class="btn-primary" type="submit">{{ $room ? 'Enregistrer' : 'Créer la chambre' }}</button>
            <a href="{{ route('host.rooms') }}" class="btn-ghost">Annuler</a>
        </div>
    </form>
    @if ($room)
        <div class="mt-10 max-w-3xl border-t border-gray-200 pt-6 dark:border-gray-800">
            <x-modal-confirm :action="route('host.rooms.destroy', $room['id'])" method="DELETE" title="Supprimer « {{ $room['name'] }} » ?" confirm="Supprimer la chambre" button="Supprimer cette chambre" button-class="btn-ghost text-red-700" danger>
                La chambre et ses photos seront supprimées. Les réservations passées restent consultables. Pour la retirer temporairement, décochez plutôt « visible et réservable ».
            </x-modal-confirm>
        </div>
    @endif
</x-layouts.panel>
