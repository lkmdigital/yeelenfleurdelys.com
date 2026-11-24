<x-layouts.panel space="admin" title="Paramètres" heading="Paramètres">
    <nav class="mb-5 flex gap-1 overflow-x-auto border-b border-gray-200 text-sm dark:border-gray-800" aria-label="Paramètres">
        @foreach ($tabs as $k => $l)
            <a href="{{ route('admin.settings', $k) }}" @class(['-mb-px whitespace-nowrap border-b-2 px-3 py-2 font-medium', 'border-primary text-primary' => $tab === $k, 'border-transparent text-gray-600 hover:text-gray-900 dark:text-gray-400' => $tab !== $k])>{{ $l }}</a>
        @endforeach
    </nav>
    <div class="max-w-3xl space-y-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="card grid gap-4 sm:grid-cols-2">
            @csrf @method('PUT') <input type="hidden" name="tab" value="{{ $tab }}">
            @if ($tab === 'general')
                <x-input name="app_name" label="Nom du site" :value="$s['app_name'] ?? ''" required />
                <x-input name="app_support_email" type="email" label="E-mail d’assistance" :value="$s['app_support_email'] ?? ''" />
                <x-input name="app_support_phone" label="Téléphone d’assistance" :value="$s['app_support_phone'] ?? ''" />
                <div></div>
                <x-input name="booking_min_nights" type="number" min="1" label="Nuits minimum par réservation" :value="$s['booking_min_nights'] ?? 1" required />
                <x-input name="booking_max_nights" type="number" min="1" label="Nuits maximum par réservation" :value="$s['booking_max_nights'] ?? 30" required />
                <x-checkbox name="maintenance_enabled" label="Mode maintenance (le site public affiche un message)" :checked="$s['maintenance_enabled'] ?? false" class="sm:col-span-2" />
                <x-textarea name="maintenance_message" label="Message de maintenance" :value="$s['maintenance_message'] ?? ''" rows="2" class="sm:col-span-2" />
            @elseif ($tab === 'facturation')
                <x-input name="billing_company_name" label="Raison sociale" :value="$s['billing_company_name'] ?? ''" />
                <x-input name="billing_rccm" label="RCCM" :value="$s['billing_rccm'] ?? ''" />
                <x-input name="billing_ncc" label="N° de compte contribuable" :value="$s['billing_ncc'] ?? ''" />
                <x-input name="vat_rate" type="number" min="0" max="100" step="0.01" label="TVA (%)" :value="$s['vat_rate'] ?? 0" required />
                <x-textarea name="billing_address" label="Adresse de facturation" :value="$s['billing_address'] ?? ''" rows="2" class="sm:col-span-2" />
                <x-checkbox name="tourist_tax_enabled" label="Appliquer une taxe de séjour" :checked="$s['tourist_tax_enabled'] ?? false" class="sm:col-span-2" />
                <x-select name="tourist_tax_mode" label="Calcul de la taxe" :options="['fixed' => 'Montant par nuit et par personne', 'percentage' => 'Pourcentage du séjour']" :value="$s['tourist_tax_mode'] ?? 'fixed'" />
                <x-input name="tourist_tax_amount" type="number" min="0" step="0.01" label="Montant ou pourcentage" :value="$s['tourist_tax_amount'] ?? 0" required />
            @elseif ($tab === 'paiements')
                <x-checkbox name="deferred_payment_enabled" label="Autoriser le paiement différé (entreprises)" :checked="$s['deferred_payment_enabled'] ?? false" class="sm:col-span-2" />
            @else
                <x-input name="loyalty_points_per_fcfa" type="number" min="0" step="0.001" label="Points gagnés par FCFA dépensé" :value="$s['loyalty_points_per_fcfa'] ?? 0" required />
                <x-input name="loyalty_voucher_validity_days" type="number" min="0" label="Validité des bons (jours)" :value="$s['loyalty_voucher_validity_days'] ?? 0" required />
                <x-input name="loyalty_first_booking_bonus" type="number" min="0" label="Bonus première réservation (points)" :value="$s['loyalty_first_booking_bonus'] ?? 0" required />
                <x-input name="loyalty_birthday_bonus" type="number" min="0" label="Bonus anniversaire (points)" :value="$s['loyalty_birthday_bonus'] ?? 0" required />
                <x-input name="loyalty_review_bonus" type="number" min="0" label="Bonus avis publié (points)" :value="$s['loyalty_review_bonus'] ?? 0" required />
            @endif
            <div class="sm:col-span-2"><button class="btn-primary" type="submit">Enregistrer</button></div>
        </form>

        @if ($tab === 'paiements')
            <section class="card">
                <h2 class="section-title">Moyens de paiement proposés aux clients</h2>
                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @foreach ($methods as $m)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <span class="font-medium">{{ $m['name'] }}</span>
                            <form method="POST" action="{{ route('admin.settings.payment-method', $m['id']) }}">
                                @csrf @method('PUT') <input type="hidden" name="is_active" value="{{ $m['is_active'] ? 0 : 1 }}">
                                <button class="{{ $m['is_active'] ? 'btn-secondary' : 'btn-primary' }} btn-sm" type="submit">{{ $m['is_active'] ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
        <p class="text-xs text-gray-500">Les clés techniques (WhatsApp, cartes, paiement, IA) ne sont pas affichées ici pour ne jamais apparaître à l’écran ; leur réglage est fait par l’équipe technique.</p>
    </div>
</x-layouts.panel>
