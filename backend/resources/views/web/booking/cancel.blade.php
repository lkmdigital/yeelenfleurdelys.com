<x-layouts.public title="Annuler la réservation">
    <div class="mx-auto max-w-4xl px-4 py-8">
        <a href="{{ route('bookings.show', $ref) }}" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" class="h-4 w-4" /> Retour à la réservation</a>
        <h1 class="page-title mt-3">Annuler la réservation</h1>
        <div class="mt-6 grid items-start gap-8 lg:grid-cols-[1fr_340px]">
            <form method="POST" action="{{ route('bookings.cancel.submit', $ref) }}" class="card space-y-4">
                @csrf
                <x-flash />
                @if (!empty($b['cancellation_policy']['label']))
                    <p class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">{{ $b['cancellation_policy']['label'] }}</p>
                @endif
                @if ($own)
                    <x-textarea name="reason" label="Motif (facultatif)" rows="3" maxlength="500" />
                @else
                    <x-input name="email" type="email" label="Adresse e-mail utilisée pour la réservation" autocomplete="email" required />
                @endif
                <p class="text-sm text-gray-600 dark:text-gray-400">L'annulation est définitive. Une réservation déjà payée s'annule en nous contactant si le bouton ci-dessous est refusé.</p>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-danger">Confirmer l'annulation</button>
                    <a href="{{ route('bookings.show', $ref) }}" class="btn-secondary">Garder ma réservation</a>
                </div>
            </form>
            <aside>@include('web.booking._summary', ['b' => $b])</aside>
        </div>
    </div>
</x-layouts.public>
