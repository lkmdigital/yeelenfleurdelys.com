@php
    use App\Support\Fmt;
    $u = $b['user'] ?? [];
    $paid = (float) ($b['amount_paid'] ?? 0);
@endphp
<x-layouts.panel space="admin" :title="'Réservation ' . ($b['booking_number'] ?? '#' . $b['id'])">
    <a href="{{ route('admin.reservations') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Réservations</a>
    <div class="mb-5 flex flex-wrap items-center gap-3"><h1 class="page-title">Réservation {{ $b['booking_number'] ?? '#' . $b['id'] }}</h1><x-status :status="$b['status']" /></div>
    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <section class="card">
                <h2 class="section-title">Séjour</h2>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Établissement</dt><dd class="font-medium"><a class="link" href="{{ route('admin.accommodations.show', $b['accommodation']['id']) }}">{{ $b['accommodation']['name'] }}</a></dd></div>
                    <div><dt class="text-gray-500">Chambre</dt><dd class="font-medium">{{ $b['room']['name'] ?? '—' }}@if (($b['rooms_quantity'] ?? 1) > 1) × {{ $b['rooms_quantity'] }}@endif</dd></div>
                    <div><dt class="text-gray-500">Arrivée</dt><dd class="font-medium">{{ Fmt::dateLong($b['check_in']) }}</dd></div>
                    <div><dt class="text-gray-500">Départ</dt><dd class="font-medium">{{ Fmt::dateLong($b['check_out']) }}</dd></div>
                    <div><dt class="text-gray-500">Voyageurs</dt><dd class="font-medium">{{ $b['guests'] }} · {{ Fmt::nights($b['check_in'], $b['check_out']) }} nuit(s)</dd></div>
                    <div><dt class="text-gray-500">Créée le</dt><dd class="font-medium">{{ Fmt::datetime($b['created_at']) }}</dd></div>
                </dl>
                @if ($b['special_requests'] ?? null)<p class="mt-3 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800/50">{{ $b['special_requests'] }}</p>@endif
            </section>
            <section class="card">
                <h2 class="section-title">Client</h2>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Nom</dt><dd class="font-medium">@if ($u['id'] ?? null)<a class="link" href="{{ route('admin.users.show', $u['id']) }}">{{ $u['name'] }}</a>@else — @endif</dd></div>
                    <div><dt class="text-gray-500">E-mail</dt><dd class="break-all font-medium">{{ $u['email'] ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $u['phone'] ?? '—' }}</dd></div>
                    @if ($b['booked_for_third_party'] ?? false)<div><dt class="text-gray-500">Voyageur</dt><dd class="font-medium">{{ $b['traveler_name'] }} · {{ $b['traveler_phone'] }}</dd></div>@endif
                </dl>
            </section>
            <section class="card">
                <h2 class="section-title">Paiements</h2>
                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($b['payments'] ?? [] as $p)
                        <li class="flex flex-wrap justify-between gap-2 py-2"><span>{{ Fmt::datetime($p['created_at']) }} · {{ $p['payment_method'] ?? '' }}</span><span class="tabular-nums">{{ Fmt::price($p['amount']) }} · {{ Fmt::paymentStatus($p['status']) }}</span></li>
                    @empty
                        <li class="py-2 text-gray-500">Aucun paiement.</li>
                    @endforelse
                </ul>
            </section>
            @if (!empty($b['history']))
                <section class="card">
                    <h2 class="section-title">Historique</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($b['history'] as $h)
                            <li><span class="text-gray-500">{{ Fmt::datetime($h['created_at']) }}</span> · {{ $h['description'] ?? ($h['action'] ?? '') }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
        <aside class="space-y-4">
            <section class="card space-y-2 text-sm">
                <h2 class="section-title">Montants</h2>
                <div class="flex justify-between"><span class="text-gray-500">Total</span><span class="font-semibold tabular-nums">{{ Fmt::price($b['total_price']) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Payé</span><span class="tabular-nums">{{ Fmt::price($paid) }}</span></div>
                @if ((float) ($b['refund_amount'] ?? 0) > 0)<div class="flex justify-between"><span class="text-gray-500">Remboursé</span><span class="tabular-nums">{{ Fmt::price($b['refund_amount']) }}</span></div>@endif
                @if ((float) ($b['credit_amount'] ?? 0) > 0)<div class="flex justify-between"><span class="text-gray-500">Avoir</span><span class="tabular-nums">{{ Fmt::price($b['credit_amount']) }}</span></div>@endif
                <div class="flex justify-between"><span class="text-gray-500">Statut</span><span>{{ Fmt::paymentStatus($b['payment_status'], $b['display_payment_status_label'] ?? null) }}</span></div>
            </section>
            <section class="card space-y-3">
                <h2 class="section-title">Actions</h2>
                @if ($b['status'] === 'awaiting_host_confirmation')
                    <form method="POST" action="{{ route('admin.reservations.act', [$b['id'], 'approve']) }}">@csrf<button class="btn-primary w-full" type="submit">Confirmer la disponibilité</button></form>
                    <form method="POST" action="{{ route('admin.reservations.act', [$b['id'], 'refuse']) }}">@csrf<button class="btn-secondary w-full" type="submit">Refuser la demande</button></form>
                @endif
                @if ($b['status'] === 'confirmed')
                    <form method="POST" action="{{ route('admin.reservations.act', [$b['id'], 'complete']) }}">@csrf<button class="btn-secondary w-full" type="submit">Marquer le séjour terminé</button></form>
                @endif
                @if (in_array($b['status'], ['pending', 'confirmed', 'awaiting_host_confirmation'], true))
                    <x-modal-confirm :action="route('admin.reservations.act', [$b['id'], 'cancel'])" title="Annuler cette réservation ?" confirm="Annuler la réservation" button="Annuler la réservation" button-class="btn-ghost w-full text-red-700" danger>
                        Le client et l’établissement sont prévenus. Un paiement éventuel est traité selon les conditions d’annulation.
                        <textarea name="reason" rows="2" class="input mt-3" placeholder="Motif (facultatif)" aria-label="Motif de l'annulation" maxlength="500"></textarea>
                    </x-modal-confirm>
                @else
                    <p class="text-sm text-gray-500">Aucune action possible sur une réservation {{ mb_strtolower(Fmt::bookingStatus($b['status'])[0]) }}.</p>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.panel>
