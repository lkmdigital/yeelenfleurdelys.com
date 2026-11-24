<x-auth-card title="Se connecter" subtitle="Clients, gérants et administration : un seul accès.">
    <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
        @csrf
        <x-input name="email" type="email" label="Adresse e-mail" autocomplete="email" required autofocus />
        <div>
            <x-input name="password" type="password" label="Mot de passe" autocomplete="current-password" required />
            <a href="{{ route('password.request') }}" class="link mt-1.5 inline-block text-sm">Mot de passe oublié ?</a>
        </div>
        <x-checkbox name="remember" label="Rester connecté sur cet appareil" />
        <button type="submit" class="btn-primary w-full">Se connecter</button>
    </form>
    <x-slot:after>
        Pas encore de compte ? <a href="{{ route('register') }}" class="link font-semibold">Créer un compte client</a>
        <p class="mt-2">Vous avez réservé sans compte ? <a href="{{ route('activate') }}" class="link">Activez votre espace</a></p>
    </x-slot:after>
</x-auth-card>
