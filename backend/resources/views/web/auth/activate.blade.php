<x-auth-card title="Activer mon espace client" subtitle="Vous avez déjà réservé chez nous sans compte ? Choisissez un mot de passe pour retrouver vos réservations.">
    <form method="POST" action="{{ route('activate.submit') }}" class="space-y-4">
        @csrf
        <x-input name="email" type="email" label="Adresse e-mail utilisée pour la réservation" :value="$email" autocomplete="email" required />
        <x-input name="name" label="Nom complet" :value="$prefill['name'] ?? null" autocomplete="name" />
        <x-input name="phone" type="tel" label="Téléphone" :value="$prefill['phone'] ?? null" autocomplete="tel" />
        <x-input name="password" type="password" label="Mot de passe" autocomplete="new-password" hint="8 caractères minimum." required />
        <x-input name="password_confirmation" type="password" label="Confirmez le mot de passe" autocomplete="new-password" required />
        <button type="submit" class="btn-primary w-full">Activer mon espace</button>
    </form>
    <x-slot:after>Déjà un mot de passe ? <a href="{{ route('login') }}" class="link">Se connecter</a></x-slot:after>
</x-auth-card>
