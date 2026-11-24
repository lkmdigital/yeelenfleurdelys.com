<x-auth-card title="Nouveau mot de passe">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ old('token', $token) }}">
        <x-input name="email" type="email" label="Adresse e-mail" :value="$email" autocomplete="email" required />
        <x-input name="password" type="password" label="Nouveau mot de passe" autocomplete="new-password" hint="8 caractères minimum." required autofocus />
        <x-input name="password_confirmation" type="password" label="Confirmez le mot de passe" autocomplete="new-password" required />
        @error('token')<p class="field-error">{{ $message }}</p>@enderror
        <button type="submit" class="btn-primary w-full">Enregistrer le mot de passe</button>
    </form>
</x-auth-card>
