@php $p = $profile; $twoOn = (bool) ($twoFactor['enabled'] ?? false); @endphp
<x-layouts.panel space="host" title="Paramètres" heading="Paramètres">
    <div class="max-w-3xl space-y-6">
        @if ($isOwner)
            <form method="POST" action="{{ route('host.settings.profile') }}" class="card grid gap-4 sm:grid-cols-2">
                @csrf @method('PUT')
                <h2 class="section-title sm:col-span-2">Coordonnées du gérant</h2>
                <x-input name="name" label="Nom complet" :value="$p['name'] ?? ''" required />
                <x-input name="phone" label="Téléphone" :value="$p['phone'] ?? ''" required />
                <x-input name="whatsapp" label="WhatsApp" :value="$p['whatsapp'] ?? ''" required />
                <x-input name="city" label="Ville" :value="$p['city'] ?? ''" />
                <x-input name="address_line1" label="Adresse" :value="$p['address_line1'] ?? ''" class="sm:col-span-2" />
                <x-input name="rccm" label="N° RCCM" :value="$p['rccm'] ?? ''" />
                <x-input name="tax_account_number" label="N° de compte contribuable" :value="$p['tax_account_number'] ?? ''" />
                <h2 class="section-title mt-2 sm:col-span-2">Compte bancaire pour les reversements</h2>
                <x-input name="bank_name" label="Banque" :value="$p['bank_name'] ?? ''" />
                <x-input name="bank_account_holder" label="Titulaire du compte" :value="$p['bank_account_holder'] ?? ''" />
                <x-input name="bank_account_number" label="RIB / numéro de compte" :value="$p['bank_account_number'] ?? ''" class="sm:col-span-2" autocomplete="off" />
                <div class="sm:col-span-2"><button class="btn-primary" type="submit">Enregistrer</button></div>
            </form>
        @endif

        <form method="POST" action="{{ route('host.settings.notifications') }}" class="card space-y-3">
            @csrf @method('PUT')
            <h2 class="section-title">Notifications</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Comment être prévenu d’une nouvelle réservation ou d’un message.</p>
            <x-checkbox name="notif_email" label="Par e-mail" :checked="$me['notif_email'] ?? true" />
            <x-checkbox name="notif_whatsapp" label="Par WhatsApp" :checked="$me['notif_whatsapp'] ?? true" />
            <x-checkbox name="notif_sms" label="Par SMS" :checked="$me['notif_sms'] ?? false" />
            <button class="btn-primary" type="submit">Enregistrer</button>
        </form>

        <form method="POST" action="{{ route('host.settings.password') }}" class="card space-y-4">
            @csrf
            <h2 class="section-title">Mot de passe</h2>
            <x-input name="current_password" type="password" label="Mot de passe actuel" autocomplete="current-password" required />
            <x-input name="password" type="password" label="Nouveau mot de passe" autocomplete="new-password" hint="8 caractères minimum." required />
            <x-input name="password_confirmation" type="password" label="Confirmez le nouveau mot de passe" autocomplete="new-password" required />
            <button class="btn-primary" type="submit">Modifier le mot de passe</button>
        </form>

        <section class="card space-y-3">
            <h2 class="section-title">Double authentification</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">À chaque connexion, un code de votre application d’authentification (Google Authenticator, Microsoft Authenticator…) vous sera demandé en plus du mot de passe.</p>
            @if (session('recovery_codes'))
                <div class="rounded-lg border border-secondary bg-secondary/10 p-3 text-sm">
                    <p class="font-semibold">Codes de secours — notez-les maintenant</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Chaque code permet une connexion si vous perdez votre téléphone. Ils ne seront plus affichés.</p>
                    <ul class="mt-2 grid grid-cols-2 gap-1 font-mono">@foreach (session('recovery_codes') as $c)<li>{{ $c }}</li>@endforeach</ul>
                </div>
            @endif
            @if ($twoOn)
                <p class="text-sm font-medium text-green-700 dark:text-green-400"><x-icon name="shield-check" class="inline h-4 w-4" /> Activée</p>
                <form method="POST" action="{{ route('host.settings.2fa', 'disable') }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <x-input name="password" type="password" label="Mot de passe pour désactiver" id="tfa_pwd" autocomplete="current-password" required />
                    <button class="btn-secondary" type="submit">Désactiver</button>
                </form>
            @elseif ($setup)
                <ol class="list-decimal space-y-2 pl-5 text-sm">
                    <li>Dans votre application, ajoutez un compte avec cette clé :<span class="mt-1 block break-all rounded bg-gray-100 px-2 py-1 font-mono dark:bg-gray-800">{{ $setup['secret'] }}</span></li>
                    <li>Saisissez le code à 6 chiffres affiché par l’application.</li>
                </ol>
                <form method="POST" action="{{ route('host.settings.2fa', 'enable') }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <input type="hidden" name="secret" value="{{ $setup['secret'] }}"><input type="hidden" name="url" value="{{ $setup['url'] }}">
                    <x-input name="code" label="Code à 6 chiffres" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required />
                    <button class="btn-primary" type="submit">Activer</button>
                </form>
            @else
                <form method="POST" action="{{ route('host.settings.2fa', 'setup') }}">@csrf<button class="btn-secondary" type="submit">Configurer</button></form>
            @endif
        </section>

        <div class="card">
            <h2 class="section-title">Apparence</h2>
            <button type="button" class="btn-secondary mt-3" @click="$store.theme.toggle()"><x-icon name="moon" class="dark:hidden" /><x-icon name="sun" class="hidden dark:block" /> Basculer clair / sombre</button>
        </div>
    </div>
</x-layouts.panel>
