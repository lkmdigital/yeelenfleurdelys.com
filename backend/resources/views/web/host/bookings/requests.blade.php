<x-layouts.panel space="host" title="Demandes à confirmer" heading="Demandes à confirmer">
    <p class="mb-4 max-w-3xl text-sm text-gray-600 dark:text-gray-400">Ces clients attendent que vous confirmiez la disponibilité. Dès votre confirmation, ils reçoivent le lien de paiement. Sans réponse, la demande expire automatiquement.</p>
    @include('web.host.bookings._table', ['emptyTitle' => 'Aucune demande en attente', 'emptyText' => 'Les nouvelles demandes de réservation apparaîtront ici.'])
    <x-pager :meta="$meta" />
</x-layouts.panel>
