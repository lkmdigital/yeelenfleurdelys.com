<x-auth-card title="Vérifiez votre adresse e-mail" :subtitle="'Nous avons envoyé un code à 6 chiffres à ' . $email . '. Il est valable 10 minutes.'">
    <form method="POST" action="{{ route('otp.submit') }}" class="space-y-4">
        @csrf
        <x-input name="code" label="Code reçu" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" required autofocus
            class="[&_input]:text-center [&_input]:text-2xl [&_input]:tracking-[0.5em]" />
        <button type="submit" class="btn-primary w-full">Valider le code</button>
    </form>
    <form method="POST" action="{{ route('otp.submit') }}" class="mt-3 text-center">
        @csrf <input type="hidden" name="resend" value="1">
        <button type="submit" class="link text-sm">Je n'ai rien reçu : renvoyer le code</button>
    </form>
    <x-slot:after>Pensez à regarder dans vos courriers indésirables.</x-slot:after>
</x-auth-card>
