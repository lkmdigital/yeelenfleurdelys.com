<x-layouts.public title="Réservation">
    <div class="mx-auto max-w-lg px-4 py-16">
        <x-empty :title="$status === 404 ? 'Réservation introuvable' : ($status === 403 ? 'Accès refusé' : 'Réservation indisponible')" icon="calendar-x">
            @if ($status === 404)
                Ce lien n'est plus valable. Retrouvez votre réservation avec son numéro et votre e-mail.
            @elseif ($status === 403)
                Ouvrez le lien reçu par e-mail, ou connectez-vous avec le compte utilisé pour réserver.
            @else
                Le service rencontre un problème momentané. Réessayez dans quelques instants (code {{ $status }}).
            @endif
            <x-slot:action>
                <a href="{{ route('booking.lookup') }}" class="btn-primary">Retrouver ma réservation</a>
            </x-slot:action>
        </x-empty>
    </div>
</x-layouts.public>
