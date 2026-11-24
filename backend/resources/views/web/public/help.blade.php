@php
    $topics = [
        ['reserver', 'Réserver', 'Envoyer une demande, la validation du gérant, réserver pour un proche.', 'calendar-check'],
        ['payer', 'Payer', 'Moyens de paiement, réduction du paiement en ligne, garantie de réservation.', 'credit-card'],
        ['annuler', 'Modifier ou annuler', "Annuler avec ou sans compte, refus du gérant, conditions d'annulation.", 'x-circle'],
        ['reservation', 'Ma réservation et mon reçu', 'Retrouver, consulter et imprimer votre réservation et votre reçu.', 'file-text'],
        ['partenaires', 'Gérants', 'Gérer son établissement et répondre aux demandes.', 'building-2'],
    ];
@endphp
<x-support-shell title="Centre d'aide" intro="Comment pouvons-nous vous aider ? Choisissez une rubrique, retrouvez votre réservation ou contactez-nous.">
    <a href="{{ route('booking.lookup') }}" class="mb-8 flex items-center gap-4 rounded-xl border border-primary/30 bg-primary/5 p-5 hover:bg-primary/10">
        <x-icon name="search" class="h-6 w-6 shrink-0 text-primary" />
        <span><span class="block font-semibold">Retrouver ma réservation</span><span class="text-sm text-gray-600 dark:text-gray-400">Avec votre numéro de réservation et votre e-mail, sans compte.</span></span>
    </a>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($topics as [$slug, $t, $d, $icon])
            <a href="{{ route('faq') }}#{{ $slug }}" class="card block hover:border-primary">
                <x-icon :name="$icon" class="mb-3 h-6 w-6 text-primary" />
                <p class="mb-1 font-semibold">{{ $t }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $d }}</p>
            </a>
        @endforeach
    </div>
    <div class="mt-12 rounded-xl bg-gray-50 p-6 dark:bg-gray-800/60">
        <p class="mb-3 font-semibold">Besoin d'une aide personnalisée ?</p>
        <ul class="space-y-1.5 text-sm">
            <li>WhatsApp : <a class="link" href="{{ $support['whatsappHref'] }}" target="_blank" rel="noopener noreferrer">écrire à l'assistance</a></li>
            <li>Téléphone : <a class="link" href="{{ $support['phoneHref'] }}">{{ $support['phone'] }}</a></li>
            <li>E-mail : <a class="link" href="{{ $support['emailHref'] }}">{{ $support['email'] }}</a></li>
            <li><a class="link" href="{{ route('contact') }}">Formulaire de contact</a> · <a class="link" href="{{ route('faq') }}">Toutes les questions fréquentes</a></li>
        </ul>
    </div>
</x-support-shell>
