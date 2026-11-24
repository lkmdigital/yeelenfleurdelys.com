@php
    use App\Support\Fmt;
    $u = $b['user'] ?? [];
    $name = ($b['traveler_name'] ?? null) ?: ($u['name'] ?? 'Client');
    $phone = ($b['traveler_phone'] ?? null) ?: ($u['phone'] ?? null);
    $wa = preg_replace('/\D+/', '', (string) (($u['whatsapp'] ?? null) ?: $phone));
    $paid = (float) ($b['amount_paid'] ?? 0);
    $total = (float) ($b['total_price'] ?? 0);
    $canCancel = in_array($b['status'], ['pending', 'confirmed'], true);
    $canComplete = $b['status'] === 'confirmed' && substr($b['check_in'], 0, 10) <= now()->toDateString();
@endphp
<x-layouts.panel space="host" :title="'Réservation ' . ($b['booking_number'] ?? '#' . $b['id'])">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('host.reservations') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Retour</a>
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <h1 class="page-title">Réservation {{ $b['booking_number'] ?? '#' . $b['id'] }}</h1>
        <x-status :status="$b['status']" />
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <section class="card">
                <h2 class="section-title">Séjour</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Arrivée</dt><dd class="font-medium">{{ Fmt::dateLong($b['check_in']) }} @if ($hostProperty['check_in_time'] ?? null) · dès {{ Fmt::time($hostProperty['check_in_time']) }} @endif</dd></div>
                    <div><dt class="text-gray-500">Départ</dt><dd class="font-medium">{{ Fmt::dateLong($b['check_out']) }}</dd></div>
                    <div><dt class="text-gray-500">Chambre</dt><dd class="font-medium">{{ $b['room']['name'] ?? '—' }}@if (($b['rooms_quantity'] ?? 1) > 1) × {{ $b['rooms_quantity'] }}@endif</dd></div>
                    <div><dt class="text-gray-500">Voyageurs</dt><dd class="font-medium">{{ $b['guests'] }} adulte(s)@if ($b['children'] ?? 0), {{ $b['children'] }} enfant(s)@endif · {{ Fmt::nights($b['check_in'], $b['check_out']) }} nuit(s)</dd></div>
                    @if ($b['estimated_arrival_time'] ?? null)<div><dt class="text-gray-500">Heure d’arrivée prévue</dt><dd class="font-medium">{{ Fmt::time($b['estimated_arrival_time']) }}</dd></div>@endif
                    @if ($b['checked_in_at'] ?? null)<div><dt class="text-gray-500">Arrivée enregistrée</dt><dd class="font-medium">{{ Fmt::datetime($b['checked_in_at']) }}</dd></div>@endif
                </dl>
                @if ($b['special_requests'] ?? null)
                    <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800/50"><p class="font-medium">Demande particulière</p><p class="mt-1 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $b['special_requests'] }}</p></div>
                @endif
            </section>

            <section class="card">
                <h2 class="section-title">Client</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Nom</dt><dd class="font-medium">{{ $name }}</dd></div>
                    @if ($phone)<div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium"><a class="link" href="tel:{{ $phone }}">{{ $phone }}</a></dd></div>@endif
                    @if ($u['email'] ?? null)<div><dt class="text-gray-500">E-mail</dt><dd class="break-all font-medium"><a class="link" href="mailto:{{ $u['email'] }}">{{ $u['email'] }}</a></dd></div>@endif
                    @if ($b['residence_country'] ?? null)<div><dt class="text-gray-500">Pays de résidence</dt><dd class="font-medium">{{ $b['residence_country'] }}@if ($b['residence_city'] ?? null), {{ $b['residence_city'] }}@endif</dd></div>@endif
                    @if ($b['booked_for_third_party'] ?? false)<div class="sm:col-span-2"><dt class="text-gray-500">Réservé par</dt><dd class="font-medium">{{ $u['name'] ?? '—' }} pour {{ $b['traveler_name'] }}</dd></div>@endif
                </dl>
                @if ($wa)
                    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn-secondary btn-sm mt-4"><x-icon name="message-circle" /> Écrire sur WhatsApp</a>
                @endif
            </section>

            <section class="card">
                <h2 class="section-title">Messages avec le client</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($messages as $m)
                        @php $mine = ($m['sender_id'] ?? null) !== ($b['user_id'] ?? null); @endphp
                        <div class="flex {{ $mine ? 'justify-end' : '' }}">
                            <div class="max-w-[85%] rounded-lg px-3 py-2 text-sm {{ $mine ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-gray-800' }}">
                                <p class="whitespace-pre-line">{{ $m['body'] }}</p>
                                <p class="mt-1 text-[11px] opacity-70">{{ Fmt::datetime($m['created_at']) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Aucun message pour le moment.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('host.bookings.message', $b['id']) }}" class="mt-4 flex gap-2">
                    @csrf
                    <input name="body" class="input" placeholder="Votre message…" maxlength="2000" required aria-label="Votre message">
                    <button class="btn-primary" type="submit">Envoyer</button>
                </form>
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
            <section class="card text-sm">
                <h2 class="section-title">Paiement</h2>
                <dl class="mt-3 space-y-2">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Montant du séjour</dt><dd class="font-semibold tabular-nums">{{ Fmt::price($total) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Déjà payé</dt><dd class="tabular-nums">{{ Fmt::price($paid) }}</dd></div>
                    @if ($paid > 0 && $paid < $total)<div class="flex justify-between gap-3"><dt class="text-gray-500">Reste à régler sur place</dt><dd class="font-semibold tabular-nums">{{ Fmt::price($total - $paid) }}</dd></div>@endif
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Statut</dt><dd>{{ Fmt::paymentStatus($b['payment_status']) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Réservée le</dt><dd>{{ Fmt::datetime($b['created_at']) }}</dd></div>
                </dl>
            </section>

            @if ($b['status'] === 'awaiting_host_confirmation')
                <section class="card space-y-3" id="refuser">
                    <h2 class="section-title">Votre réponse</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Le client attend votre confirmation pour payer.</p>
                    <form method="POST" action="{{ route('host.bookings.act', [$b['id'], 'approve']) }}">@csrf<button class="btn-primary w-full" type="submit">Confirmer la disponibilité</button></form>
                    <form method="POST" action="{{ route('host.bookings.act', [$b['id'], 'refuse']) }}" class="space-y-2">
                        @csrf
                        <x-textarea name="reason" label="Motif du refus (facultatif)" rows="2" />
                        <button class="btn-secondary w-full" type="submit">Refuser la demande</button>
                    </form>
                </section>
            @endif

            @if ($canComplete || $canCancel)
                <section class="card space-y-3">
                    <h2 class="section-title">Actions</h2>
                    @if ($canComplete)
                        <form method="POST" action="{{ route('host.bookings.act', [$b['id'], 'complete']) }}">@csrf<button class="btn-primary w-full" type="submit">Marquer le séjour terminé</button></form>
                    @endif
                    @if ($canCancel)
                        <x-modal-confirm :action="route('host.bookings.act', [$b['id'], 'cancel'])" title="Annuler cette réservation ?" confirm="Annuler la réservation" button="Annuler la réservation" button-class="btn-secondary w-full text-red-700" danger>
                            Le client est prévenu par e-mail. Si un paiement a été fait, il est remboursé selon les conditions d’annulation.
                            <textarea name="reason" rows="2" class="input mt-3" placeholder="Motif (facultatif)" aria-label="Motif de l'annulation" maxlength="500"></textarea>
                        </x-modal-confirm>
                    @endif
                </section>
            @endif
        </aside>
    </div>
</x-layouts.panel>
