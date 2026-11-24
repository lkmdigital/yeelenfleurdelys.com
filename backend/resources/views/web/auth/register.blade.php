<x-auth-card title="Créer un compte client" subtitle="Retrouvez vos réservations, vos factures et vos avantages fidélité.">
    <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input name="first_name" label="Prénom" autocomplete="given-name" required />
            <x-input name="last_name" label="Nom" autocomplete="family-name" required />
        </div>
        <x-input name="email" type="email" label="Adresse e-mail" autocomplete="email" :value="$email" required />
        <x-input name="phone" type="tel" label="Téléphone" autocomplete="tel" placeholder="07 00 00 00 00" required />
        <x-input name="password" type="password" label="Mot de passe" autocomplete="new-password" hint="8 caractères minimum." required />
        <x-input name="password_confirmation" type="password" label="Confirmez le mot de passe" autocomplete="new-password" required />
        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" name="accept_terms" value="1" @checked(old('accept_terms')) required class="mt-0.5 h-4 w-4 accent-[rgb(var(--brand))]">
            <span>J'accepte les <a href="{{ route('legal', 'cgu') }}" target="_blank" class="link">conditions générales d'utilisation</a> et la <a href="{{ route('legal', 'confidentialite') }}" target="_blank" class="link">politique de confidentialité</a>.</span>
        </label>
        @error('accept_terms')<p class="field-error">{{ $message }}</p>@enderror
        <button type="submit" class="btn-primary w-full">Créer mon compte</button>
    </form>
    <x-slot:after>Déjà inscrit ? <a href="{{ route('login') }}" class="link font-semibold">Se connecter</a></x-slot:after>
</x-auth-card>
