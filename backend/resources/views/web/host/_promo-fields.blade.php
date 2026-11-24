{{-- Champs d'une promotion (création ou modification). $p = promotion existante ou null. --}}
@php $p = $p ?? []; $sfx = isset($p['id']) ? '_' . $p['id'] : ''; @endphp
<div class="grid gap-3 sm:grid-cols-2" x-data="{ type: @js($p['discount_type'] ?? 'percent') }">
    <x-select name="discount_type" :id="'dt' . $sfx" label="Type de réduction" :options="['percent' => 'Pourcentage', 'fixed' => 'Montant par nuit']" :value="$p['discount_type'] ?? 'percent'" x-model="type" />
    <div x-show="type === 'percent'"><x-input name="discount_percent" :id="'dp' . $sfx" type="number" min="1" max="100" label="Réduction (%)" :value="isset($p['discount_percent']) ? (float) $p['discount_percent'] : ''" /></div>
    <div x-show="type === 'fixed'" x-cloak><x-input name="discount_amount" :id="'da' . $sfx" type="number" min="1" label="Réduction (FCFA)" :value="isset($p['discount_amount']) ? (int) $p['discount_amount'] : ''" /></div>
    <x-input name="start_date" :id="'sd' . $sfx" type="date" label="Du" :value="isset($p['start_date']) ? substr($p['start_date'], 0, 10) : ''" required />
    <x-input name="end_date" :id="'ed' . $sfx" type="date" label="Au" :value="isset($p['end_date']) ? substr($p['end_date'], 0, 10) : ''" required />
    <x-select name="room_id" :id="'rm' . $sfx" label="Chambre concernée" :options="$rooms" placeholder="Toutes les chambres" :value="$p['room_id'] ?? ''" />
    <x-input name="min_stay_nights" :id="'ms' . $sfx" type="number" min="1" label="Séjour minimum (nuits)" :value="$p['min_stay_nights'] ?? ''" />
    <x-input name="promo_code" :id="'pc' . $sfx" label="Code promo (facultatif)" :value="$p['promo_code'] ?? ''" hint="Vide = réduction appliquée automatiquement." class="uppercase" />
    <x-input name="description" :id="'de' . $sfx" label="Description" :value="$p['description'] ?? ''" placeholder="Offre spéciale Tabaski" />
    <x-checkbox name="members_only" :id="'mo' . $sfx" label="Réservée aux clients inscrits" :checked="$p['members_only'] ?? false" class="sm:col-span-2" />
</div>
