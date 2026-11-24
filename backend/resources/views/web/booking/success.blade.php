<x-layouts.public title="Réservation confirmée">
    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-700"><x-icon name="check" class="h-7 w-7" /></span>
            <h1 class="page-title mt-4">Merci, votre paiement est enregistré !</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Un e-mail de confirmation avec votre reçu vient de vous être envoyé.</p>
            @if (!empty($b['confirmation_code']))
                <p class="mt-4 inline-block rounded-lg bg-secondary px-4 py-2 font-mono text-lg font-bold text-onaccent">Code : {{ $b['confirmation_code'] }}</p>
                <p class="mt-1 text-xs text-gray-500">À présenter à votre arrivée.</p>
            @endif
        </div>
        <div class="mt-8">@include('web.booking._summary', ['b' => $b])</div>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('bookings.show', $ref) }}" class="btn-primary">Voir ma réservation</a>
            @guest<a href="{{ route('activate', ['email' => $b['user']['email'] ?? null]) }}" class="btn-secondary">Créer mon espace client</a>@endguest
        </div>
    </div>
</x-layouts.public>
