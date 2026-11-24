@php
    use App\Support\Fmt;
    $nights = Fmt::nights($search['check_in'], $search['check_out']);
    $label = $room['name'] ?? 'Séjour';
    $img = Fmt::coverOrNull($room ?? []) ?? Fmt::coverOrNull($acc);
    $hours = $quote['cancellation_policy_hours'] ?? $acc['cancellation_policy_hours'] ?? 48;
@endphp
<x-layouts.public title="Réserver">
    <div class="mx-auto max-w-6xl px-4 py-8">
        <a href="{{ route('accommodations.show', ['id' => $acc['id'], 'check_in' => $search['check_in'], 'check_out' => $search['check_out'], 'guests' => $search['guests']]) }}#chambres" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" class="h-4 w-4" /> {{ $acc['name'] }}</a>
        <h1 class="page-title mt-3">Finaliser votre réservation</h1>

        <div class="mt-6 grid items-start gap-8 lg:grid-cols-[1fr_360px]">
            <div class="min-w-0 space-y-6">
                <x-flash />

                {{-- 1. Dates (formulaire séparé : seules les dates passent dans l'adresse) --}}
                <section class="card">
                    <h2 class="section-title flex items-center gap-2"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm text-white">1</span> Votre séjour</h2>
                    <form method="GET" action="{{ route('bookings.new') }}" class="mt-4 grid gap-3 sm:grid-cols-4 sm:items-end">
                        @if ($room)<input type="hidden" name="room_id" value="{{ $room['id'] }}">@else<input type="hidden" name="accommodation_id" value="{{ $acc['id'] }}">@endif
                        <x-input name="check_in" type="date" label="Arrivée" :value="$search['check_in']" :min="now()->toDateString()" required />
                        <x-input name="check_out" type="date" label="Départ" :value="$search['check_out']" :min="now()->addDay()->toDateString()" required />
                        <x-input name="guests" type="number" label="Voyageurs" :value="$search['guests']" min="1" :max="$room['capacity'] ?? 20" />
                        <button type="submit" class="btn-secondary">Mettre à jour</button>
                    </form>
                    @if (!$search['dates_ok'])
                        <p class="mt-3 text-sm text-gray-600">Dates proposées par défaut : modifiez-les puis cliquez sur « Mettre à jour ».</p>
                    @endif
                    @if ($quoteError)
                        <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $quoteError }}</p>
                    @endif
                </section>

                <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6" x-data="{ third: {{ old('booked_for_third_party') ? 'true' : 'false' }} }">
                    @csrf
                    <input type="hidden" name="accommodation_id" value="{{ $acc['id'] }}">
                    <input type="hidden" name="room_id" value="{{ $room['id'] ?? '' }}">
                    <input type="hidden" name="check_in" value="{{ $search['check_in'] }}">
                    <input type="hidden" name="check_out" value="{{ $search['check_out'] }}">
                    <input type="hidden" name="guests" value="{{ $search['guests'] }}">
                    @error('check_in')<p class="field-error">{{ $message }}</p>@enderror

                    <section class="card space-y-4">
                        <h2 class="section-title flex items-center gap-2"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm text-white">2</span> Vos coordonnées</h2>
                        @if ($user)
                            <p class="text-sm text-gray-600 dark:text-gray-400">Réservation au nom de <strong>{{ $user->name }}</strong> ({{ $user->email }}).</p>
                        @else
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-input name="first_name" label="Prénom" autocomplete="given-name" required />
                                <x-input name="last_name" label="Nom" autocomplete="family-name" required />
                            </div>
                            <x-input name="email" type="email" label="Adresse e-mail" autocomplete="email" hint="La confirmation et le reçu vous sont envoyés à cette adresse." required />
                            <p class="text-sm text-gray-600">Déjà client ? <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="link">Connectez-vous</a> pour retrouver la réservation dans votre espace.</p>
                        @endif
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-input name="phone" type="tel" label="Téléphone (WhatsApp de préférence)" :value="$user?->phone" autocomplete="tel" placeholder="07 00 00 00 00" required />
                            <x-input name="residence_country" label="Pays de résidence" :value="$user?->residence_country ?? 'Côte d\'Ivoire'" autocomplete="country-name" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-input name="residence_city" label="Ville" :value="$user?->residence_city" autocomplete="address-level2" />
                            <x-input name="estimated_arrival_time" type="time" label="Heure d'arrivée prévue" hint="Facultatif" />
                        </div>
                        <label class="flex items-start gap-2.5 text-sm">
                            <input type="hidden" name="booked_for_third_party" value="0">
                            <input type="checkbox" name="booked_for_third_party" value="1" x-model="third" class="mt-0.5 h-4 w-4 accent-[rgb(var(--brand))]">
                            <span>Je réserve pour une autre personne</span>
                        </label>
                        <div x-cloak x-show="third" class="grid gap-4 sm:grid-cols-2">
                            <x-input name="traveler_name" label="Nom de la personne qui séjourne" />
                            <x-input name="traveler_phone" type="tel" label="Son téléphone" />
                        </div>
                    </section>

                    <section class="card space-y-4">
                        <h2 class="section-title flex items-center gap-2"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm text-white">3</span> Précisions</h2>
                        <x-textarea name="special_requests" label="Demandes particulières" rows="3" hint="Facultatif : heure d'arrivée tardive, lit bébé…" maxlength="1000" />
                        <x-input name="promo_code" label="Code promo" hint="Facultatif" />
                        <label class="flex items-start gap-2.5 text-sm">
                            <input type="checkbox" name="accept_terms" value="1" @checked(old('accept_terms')) required class="mt-0.5 h-4 w-4 accent-[rgb(var(--brand))]">
                            <span>J'accepte les <a href="{{ route('legal', 'cgv') }}" target="_blank" class="link">conditions générales de vente</a> et la politique d'annulation de l'établissement.</span>
                        </label>
                        @error('accept_terms')<p class="field-error">{{ $message }}</p>@enderror
                        {{-- Mobile : le récapitulatif est sous le formulaire, on rappelle le total juste avant de confirmer. --}}
                        @if ($quote)
                            <div class="flex items-baseline justify-between rounded-lg bg-gray-50 px-4 py-3 lg:hidden dark:bg-gray-800/60">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total · {{ $quote['nights'] }} nuit(s)</span>
                                <span class="text-lg font-bold">{{ Fmt::price($quote['total']) }}</span>
                            </div>
                        @endif
                        <button type="submit" class="btn-primary w-full sm:w-auto" @disabled(!$quote)>Confirmer la réservation <x-icon name="arrow-right" /></button>
                        <p class="text-xs text-gray-500">Vous choisissez le moyen de paiement à l'étape suivante.</p>
                    </section>
                </form>
            </div>

            <aside class="card space-y-4 lg:sticky lg:top-20">
                <div class="flex gap-3">
                    @if ($img)<img src="{{ $img }}" alt="" class="h-16 w-20 rounded-lg object-cover">@endif
                    <div>
                        <p class="font-semibold">{{ $acc['name'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</p>
                    </div>
                </div>
                <dl class="space-y-1.5 border-t border-gray-200 pt-4 text-sm dark:border-gray-800">
                    <div class="flex justify-between"><dt>Arrivée</dt><dd class="font-medium">{{ Fmt::dateLong($search['check_in']) }}</dd></div>
                    <div class="flex justify-between"><dt>Départ</dt><dd class="font-medium">{{ Fmt::dateLong($search['check_out']) }}</dd></div>
                    <div class="flex justify-between"><dt>Durée</dt><dd>{{ $nights }} nuit(s)</dd></div>
                    <div class="flex justify-between"><dt>Voyageurs</dt><dd>{{ $search['guests'] }}</dd></div>
                </dl>
                @if ($quote)
                    <dl class="space-y-1.5 border-t border-gray-200 pt-4 text-sm dark:border-gray-800">
                        <div class="flex justify-between"><dt>{{ Fmt::price($quote['effective_price_per_night']) }} × {{ $quote['nights'] }} nuit(s)</dt><dd>{{ Fmt::price($quote['effective_price_per_night'] * $quote['nights']) }}</dd></div>
                        @if (!empty($quote['long_stay_discount']))
                            <div class="flex justify-between text-green-700"><dt>{{ $quote['long_stay_discount']['label'] }}</dt><dd>− {{ Fmt::price($quote['long_stay_discount']['discount_amount']) }}</dd></div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold dark:border-gray-800"><dt>Total</dt><dd>{{ Fmt::price($quote['total']) }}</dd></div>
                    </dl>
                    @if (!empty($quote['payment_options']['options']['full']['discount_percent']))
                        <p class="rounded-lg bg-green-50 p-3 text-sm text-green-800">Payez tout en ligne : {{ $quote['payment_options']['options']['full']['discount_percent'] }} % de réduction ({{ Fmt::price($quote['payment_options']['options']['full']['amount']) }}).</p>
                    @endif
                @endif
                <p class="flex gap-2 text-sm text-gray-600 dark:text-gray-400"><x-icon name="calendar-x" class="mt-0.5 h-4 w-4 shrink-0" /> {{ $hours == 0 ? 'Non remboursable.' : "Annulation gratuite jusqu'à {$hours} h avant l'arrivée." }}</p>
            </aside>
        </div>
    </div>
</x-layouts.public>
