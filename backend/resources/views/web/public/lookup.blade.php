<x-auth-card title="Retrouver ma réservation" subtitle="Saisissez votre numéro de réservation (ou votre code Yeelen & Fleur de Lys) et l'adresse e-mail utilisée lors de la réservation.">
    <form method="POST" action="{{ route('booking.lookup.submit') }}" class="space-y-4">
        @csrf
        <x-input name="reference" label="Numéro de réservation" placeholder="YF-2026-000123" required autofocus />
        <x-input name="email" type="email" label="Adresse e-mail" autocomplete="email" required />
        <button type="submit" class="btn-primary w-full">Afficher ma réservation</button>
    </form>
    <x-slot:after>Vous avez un compte ? <a href="{{ route('login') }}" class="link">Connectez-vous</a> pour voir toutes vos réservations.</x-slot:after>
</x-auth-card>
