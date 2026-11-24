<x-auth-card title="Activer votre accès" :subtitle="$info ? 'Invitation de ' . ($info['owner_name'] ?? 'votre établissement') . ($info['role_label'] ?? null ? ' — poste : ' . $info['role_label'] : '') : null">
    @if ($invalid)
        <x-empty title="Ce lien d'invitation est invalide ou a déjà été utilisé." icon="link">
            <p class="text-sm text-gray-600">Demandez au gérant de vous renvoyer une invitation.</p>
        </x-empty>
    @else
        <form method="POST" action="{{ route('activate-staff.submit') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @if (!empty($info['email']))<p class="text-sm">Compte : <strong>{{ $info['email'] }}</strong></p>@endif
            <x-input name="password" type="password" label="Choisissez un mot de passe" autocomplete="new-password" hint="8 caractères minimum." required autofocus />
            <x-input name="password_confirmation" type="password" label="Confirmez le mot de passe" autocomplete="new-password" required />
            @error('token')<p class="field-error">{{ $message }}</p>@enderror
            <button type="submit" class="btn-primary w-full">Activer mon accès</button>
        </form>
    @endif
</x-auth-card>
