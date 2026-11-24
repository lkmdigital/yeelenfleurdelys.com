@php
    use App\Support\Fmt;
    $t = fn ($v) => $v ? substr($v, 0, 5) : null;
    [$statusText, $statusCls] = Fmt::accommodationStatus($acc['status'] ?? null);
@endphp
<x-layouts.panel space="host" title="Mon établissement">
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <h1 class="page-title">Mon établissement</h1>
        <span class="badge {{ $statusCls }}">{{ $statusText }}</span>
        <a href="{{ route('accommodations.show', $acc['id']) }}" target="_blank" class="btn-ghost btn-sm ml-auto"><x-icon name="external-link" /> Voir la page publique</a>
    </div>

    @if ($readiness && !$readiness['ready'])
        <div class="card mb-6 border-secondary/60 bg-secondary/10 text-sm">
            <p class="font-semibold">Pour être visible et recevoir des réservations :</p>
            <ul class="mt-2 grid gap-1.5 sm:grid-cols-2">
                @foreach ($readiness['checks'] as $c)
                    <li class="flex items-start gap-2"><x-icon :name="$c['ok'] ? 'check-circle' : 'circle'" class="mt-0.5 h-4 w-4 shrink-0 {{ $c['ok'] ? 'text-green-600' : 'text-gray-400' }}" /> {{ $c['label'] }}</li>
                @endforeach
            </ul>
            @if (in_array($acc['status'], ['pending', 'rejected', 'unavailable'], true) && empty($readiness['submitted_for_review_at']))
                <form method="POST" action="{{ route('host.property.submit') }}" class="mt-3">@csrf<button class="btn-primary btn-sm" type="submit">Envoyer ma fiche pour validation</button></form>
            @endif
        </div>
    @endif

    <section class="card mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="section-title">Photos <span class="text-sm font-normal text-gray-500">({{ count($images) }})</span></h2>
            <form method="POST" action="{{ route('host.property.media') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="file" name="media[]" accept="image/jpeg,image/png,image/webp" multiple required class="text-sm" aria-label="Choisir des photos">
                <button class="btn-primary btn-sm" type="submit"><x-icon name="upload" /> Ajouter</button>
            </form>
        </div>
        @error('media')<p class="field-error">{{ $message }}</p>@enderror
        @error('media.*')<p class="field-error">{{ $message }}</p>@enderror
        <p class="hint">Jusqu’à 10 photos à la fois, 10 Mo maximum chacune. La photo principale s’affiche en premier sur le site.</p>
        @if (count($images))
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($images as $img)
                    <figure class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                        <img src="{{ Fmt::img($img->url) }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy">
                        <figcaption class="flex items-center justify-between gap-1 p-2">
                            @if ($img->is_primary)
                                <span class="badge bg-secondary text-onaccent">Principale</span>
                            @else
                                <form method="POST" action="{{ route('host.property.media.primary', $img->id) }}">@csrf<button class="btn-ghost btn-sm px-2" type="submit">Mettre en avant</button></form>
                            @endif
                            <x-modal-confirm :action="route('host.property.media.delete', $img->id)" method="DELETE" title="Supprimer cette photo ?" confirm="Supprimer la photo" button="Supprimer" button-class="btn-ghost btn-sm px-2 text-red-700" danger>La photo sera retirée du site.</x-modal-confirm>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @else
            <x-empty class="mt-4" title="Aucune photo" icon="image">Ajoutez au moins 5 photos : façade, accueil, chambres, espaces communs.</x-empty>
        @endif
    </section>

    <form method="POST" action="{{ route('host.property.update') }}" class="space-y-6" x-data="{ deposit: @js((bool) old('deposit_required', $acc['deposit_required'])), mode: @js(old('deposit_amount', $acc['deposit_amount'] ?? 'first_night')) }">
        @csrf @method('PUT')
        <section class="card grid gap-4 sm:grid-cols-2">
            <h2 class="section-title sm:col-span-2">Informations</h2>
            <x-input name="name" label="Nom de l’établissement" :value="$acc['name']" required />
            <x-input name="whatsapp" label="Numéro WhatsApp" :value="$acc['whatsapp']" hint="Les clients vous écrivent sur ce numéro." />
            <x-input name="address" label="Adresse" :value="$acc['address']" required class="sm:col-span-2" />
            <x-input name="city" label="Ville" :value="$acc['city']" required />
            <x-input name="price_per_night" type="number" min="0" step="500" label="Prix de base par nuit (FCFA)" :value="(int) $acc['price_per_night']" hint="Affiché « à partir de » sur le site." required />
            <x-textarea name="description" label="Description" :value="$acc['description']" rows="6" required class="sm:col-span-2" />
        </section>

        <section class="card grid gap-4 sm:grid-cols-2">
            <h2 class="section-title sm:col-span-2">Horaires et conditions</h2>
            <x-input name="check_in_time" type="time" label="Arrivée à partir de" :value="$t($acc['check_in_time'])" />
            <x-input name="check_out_time" type="time" label="Départ avant" :value="$t($acc['check_out_time'])" />
            <x-select name="cancellation_policy_hours" label="Annulation gratuite jusqu’à" :value="$acc['cancellation_policy_hours']" :options="[0 => 'Non remboursable', 24 => '24 h avant l’arrivée', 48 => '48 h avant l’arrivée', 72 => '72 h avant l’arrivée', 168 => '7 jours avant l’arrivée']" />
            <div>
                <x-checkbox name="deposit_required" label="Demander une garantie en ligne" :checked="$acc['deposit_required']" x-model="deposit" hint="Le reste est réglé sur place." />
                <div x-show="deposit" class="mt-3 space-y-3">
                    <x-select name="deposit_amount" label="Montant de la garantie" :value="$acc['deposit_amount'] ?? 'first_night'" :options="['first_night' => 'La 1re nuit', 'percentage' => 'Un pourcentage du séjour', 'fixed' => 'Un montant fixe']" x-model="mode" />
                    <div x-show="mode === 'percentage'"><x-input name="deposit_percentage" type="number" min="1" max="99" label="Pourcentage (%)" :value="$acc['deposit_percentage']" /></div>
                    <div x-show="mode === 'fixed'"><x-input name="deposit_fixed_amount" type="number" min="1" label="Montant (FCFA)" :value="$acc['deposit_fixed_amount'] ? (int) $acc['deposit_fixed_amount'] : null" /></div>
                </div>
            </div>
            <x-textarea name="special_conditions" label="Conditions particulières" :value="$acc['special_conditions']" rows="3" class="sm:col-span-2" hint="Exemple : pièce d’identité demandée à l’arrivée." />
        </section>

        <section class="card">
            <h2 class="section-title">Services et équipements</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <x-checkbox name="reception_24h" label="Réception 24 h/24" :checked="$acc['reception_24h']" />
                <x-checkbox name="breakfast_included" label="Petit-déjeuner inclus" :checked="$acc['breakfast_included']" />
                <x-checkbox name="shuttle_service" label="Navette" :checked="$acc['shuttle_service']" />
                <x-checkbox name="laundry" label="Blanchisserie" :checked="$acc['laundry']" />
                <x-checkbox name="pets_allowed" label="Animaux acceptés" :checked="$acc['pets_allowed']" />
                <x-checkbox name="smoking_area" label="Espace fumeur" :checked="$acc['smoking_area']" />
            </div>
            <x-input name="breakfast_price" type="number" min="0" step="500" label="Prix du petit-déjeuner (FCFA)" :value="$acc['breakfast_price'] ? (int) $acc['breakfast_price'] : null" class="mt-4 max-w-xs" />
            @php $current = array_map('mb_strtolower', old('amenities', $acc['amenities'] ?? [])); @endphp
            <fieldset class="mt-5">
                <legend class="label">Équipements affichés sur la page</legend>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($amenityKeys as $k)
                        <label class="flex items-center gap-2.5 text-sm"><input type="checkbox" name="amenities[]" value="{{ $k }}" @checked(in_array($k, $current, true)) class="h-4 w-4 rounded border-gray-300 accent-[rgb(var(--brand))]"> {{ Fmt::amenity($k) }}</label>
                    @endforeach
                </div>
            </fieldset>
        </section>

        <div class="sticky bottom-0 -mx-4 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 lg:-mx-6 lg:px-6">
            <button class="btn-primary" type="submit">Enregistrer la fiche</button>
        </div>
    </form>
</x-layouts.panel>
