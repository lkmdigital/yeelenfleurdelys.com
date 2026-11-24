<x-auth-card title="Vérification en deux étapes" subtitle="Saisissez le code à 6 chiffres affiché par votre application d'authentification.">
    <form method="POST" action="{{ route('2fa.submit') }}" class="space-y-4">
        @csrf
        <x-input name="code" label="Code de vérification" inputmode="numeric" autocomplete="one-time-code" maxlength="12" required autofocus
            hint="Vous pouvez aussi saisir un code de secours (8 caractères)." />
        <button type="submit" class="btn-primary w-full">Vérifier</button>
    </form>
    <x-slot:after><a href="{{ route('login') }}" class="link">Retour à la connexion</a></x-slot:after>
</x-auth-card>
