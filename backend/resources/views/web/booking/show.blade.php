@php
    use App\Support\Fmt;
    [$label, $cls] = Fmt::bookingStatus($b['status']);
    $acc = $b['accommodation'] ?? [];
    $canPay = in_array($b['status'], ['pending', 'confirmed'], true) && !in_array($b['payment_status'], ['paid', 'partial', 'refunded'], true);
    $canCancel = in_array($b['status'], ['pending', 'confirmed', 'awaiting_host_confirmation'], true) && \Carbon\Carbon::parse($b['check_in'])->isFuture();
    $wa = !empty($acc['whatsapp']) ? preg_replace('/\D/', '', $acc['whatsapp']) : null;
    if ($wa && strlen($wa) === 10) $wa = '225' . $wa;
@endphp
<x-layouts.public :title="'Réservation ' . ($b['booking_number'] ?? '')">
    <div class="mx-auto max-w-5xl px-4 py-8">
        @auth<a href="{{ route('client.reservations') }}" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" class="h-4 w-4" /> Mes réservations</a>@endauth
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <h1 class="page-title">Réservation {{ $b['booking_number'] ?? '' }}</h1>
            <span class="badge {{ $cls }}">{{ $label }}</span>
        </div>
        <div class="mt-6 grid items-start gap-8 lg:grid-cols-[1fr_340px]">
            <div class="min-w-0 space-y-6">
                <x-flash />
                @if ($b['status'] === 'awaiting_host_confirmation')
                    <div class="card border-orange-200 bg-orange-50 dark:border-orange-900 dark:bg-orange-900/20">
                        <p class="font-semibold">Demande envoyée à l'établissement</p>
                        <p class="mt-1 text-sm">Le gérant confirme la disponibilité pour vos dates. Vous recevez ensuite un e-mail avec le lien de paiement.@if (!empty($b['expires_at'])) Réponse attendue avant le {{ Fmt::datetime($b['expires_at']) }}.@endif</p>
                    </div>
                @elseif ($canPay)
                    <div class="card border-yellow-200 bg-yellow-50 dark:border-yellow-900 dark:bg-yellow-900/20">
                        <p class="font-semibold">Paiement en attente</p>
                        <p class="mt-1 text-sm">Votre réservation est disponible. Réglez-la pour la confirmer{{ !empty($b['expires_at']) ? ' avant le ' . Fmt::datetime($b['expires_at']) : '' }}.</p>
                        <a href="{{ route('bookings.payment', $ref) }}" class="btn-primary mt-3">Payer maintenant</a>
                    </div>
                @elseif ($b['status'] === 'confirmed')
                    <div class="card border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20">
                        <p class="font-semibold">Réservation confirmée</p>
                        @if (!empty($b['confirmation_code']))<p class="mt-1 text-sm">Code à présenter à l'arrivée : <strong class="font-mono text-base">{{ $b['confirmation_code'] }}</strong></p>@endif
                        @if (!empty($b['assigned_room_number']))<p class="mt-1 text-sm">Chambre attribuée : <strong>{{ $b['assigned_room_number'] }}</strong></p>@endif
                    </div>
                @endif

                <section class="card">
                    <h2 class="section-title">Détails</h2>
                    <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500">Client</dt><dd class="font-medium">{{ $b['user']['name'] ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Téléphone</dt><dd>{{ $b['user']['phone'] ?? '—' }}</dd></div>
                        @if (!empty($b['booked_for_third_party']))
                            <div><dt class="text-gray-500">Séjourne</dt><dd>{{ $b['traveler_name'] }} · {{ $b['traveler_phone'] }}</dd></div>
                        @endif
                        <div><dt class="text-gray-500">Paiement</dt><dd>{{ Fmt::paymentStatus($b['payment_status']) }}</dd></div>
                        <div><dt class="text-gray-500">Réservée le</dt><dd>{{ Fmt::datetime($b['created_at']) }}</dd></div>
                        @if (!empty($b['special_requests']))
                            <div class="sm:col-span-2"><dt class="text-gray-500">Demandes particulières</dt><dd class="whitespace-pre-line">{{ $b['special_requests'] }}</dd></div>
                        @endif
                        @if (!empty($b['cancellation_policy']['label']))
                            <div class="sm:col-span-2"><dt class="text-gray-500">Annulation</dt><dd>{{ $b['cancellation_policy']['label'] }}</dd></div>
                        @endif
                    </dl>
                </section>

                @if (is_array($messages))
                    <section class="card">
                        <h2 class="section-title">Messages avec l'établissement</h2>
                        <div class="mt-4 space-y-3">
                            @forelse ($messages as $m)
                                @php $mine = ($m['sender_id'] ?? null) === auth()->id(); @endphp
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
                        <form method="POST" action="{{ route('bookings.message', $ref) }}" class="mt-4 flex gap-2">
                            @csrf
                            <input name="body" class="input" placeholder="Votre message…" maxlength="2000" required aria-label="Votre message">
                            <button class="btn-primary" type="submit">Envoyer</button>
                        </form>
                    </section>
                @endif
            </div>
            <aside class="space-y-4 lg:sticky lg:top-20">
                @include('web.booking._summary', ['b' => $b])
                <div class="card space-y-2 text-sm">
                    @if ($wa)
                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:text-primary"><x-icon name="message-circle" class="h-4 w-4 text-primary" /> Contacter l'établissement</a>
                    @endif
                    <button type="button" onclick="window.print()" class="flex items-center gap-2 hover:text-primary"><x-icon name="printer" class="h-4 w-4 text-primary" /> Imprimer</button>
                    @if ($canCancel)
                        <a href="{{ route('bookings.cancel', $ref) }}" class="flex items-center gap-2 text-red-700 hover:underline"><x-icon name="calendar-x" class="h-4 w-4" /> Annuler la réservation</a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layouts.public>
