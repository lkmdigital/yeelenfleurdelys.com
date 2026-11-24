<x-auth-card title="Mot de passe oublié" subtitle="Indiquez votre adresse e-mail : nous vous envoyons un lien pour en choisir un nouveau.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-input name="email" type="email" label="Adresse e-mail" autocomplete="email" required autofocus />
        <button type="submit" class="btn-primary w-full">Envoyer le lien</button>
    </form>
    <x-slot:after><a href="{{ route('login') }}" class="link">Retour à la connexion</a></x-slot:after>
</x-auth-card>
