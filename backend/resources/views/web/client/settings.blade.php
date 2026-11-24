<x-layouts.panel space="client" title="Paramètres" heading="Paramètres">
    <div class="max-w-2xl space-y-6">
        <form method="POST" action="{{ route('client.settings.password') }}" class="card space-y-4">
            @csrf
            <h2 class="section-title">Mot de passe</h2>
            <x-input name="current_password" type="password" label="Mot de passe actuel" autocomplete="current-password" required />
            <x-input name="password" type="password" label="Nouveau mot de passe" autocomplete="new-password" hint="8 caractères minimum." required />
            <x-input name="password_confirmation" type="password" label="Confirmez le nouveau mot de passe" autocomplete="new-password" required />
            <button class="btn-primary" type="submit">Modifier le mot de passe</button>
        </form>
        <div class="card">
            <h2 class="section-title">Apparence</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Choisissez un affichage clair ou sombre.</p>
            <button type="button" class="btn-secondary mt-3" @click="$store.theme.toggle()"><x-icon name="moon" class="dark:hidden" /><x-icon name="sun" class="hidden dark:block" /> Basculer clair / sombre</button>
        </div>
        <div class="card">
            <h2 class="section-title">Compte</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pour supprimer votre compte et vos données, écrivez-nous depuis la <a href="{{ route('contact') }}" class="link">page contact</a>.</p>
        </div>
    </div>
</x-layouts.panel>
