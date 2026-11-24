@php
    use App\Support\Fmt;
    $price = $room['effective_price_per_night'] ?? $room['price_per_night'];
    $quantity = max(1, (int) ($room['quantity'] ?? 1));
    $wa = !empty($acc['whatsapp']) ? preg_replace('/\D/', '', $acc['whatsapp']) : null;
    if ($wa && strlen($wa) === 10) $wa = '225' . $wa;
    $cfg = [
        'accId' => $acc['id'],
        'roomId' => $room['id'],
        'capacity' => max(1, (int) $room['capacity']),
        'quantity' => $quantity,
        'checkIn' => $search['check_in'],
        'checkOut' => $search['check_out'],
        'guests' => $search['guests'],
        'rooms' => max(1, min($quantity, (int) request('rooms_quantity', 1))),
        'bookUrl' => route('bookings.new'),
    ];
    $all = array_unique(array_merge($room['amenities'] ?? [], $room['basic_amenities'] ?? [], $room['premium_amenities'] ?? []), SORT_REGULAR);
@endphp
<x-layouts.public :title="$room['name'] . ' — ' . $acc['name']">
    {{-- Parcours : établissement → chambre (ici : dates, personnes, prix) → coordonnées → paiement. --}}
    <div class="mx-auto max-w-6xl px-4 pt-6 md:py-8" @if ($price > 0) x-data="stay(@js($cfg))" @endif>
        <a href="{{ route('accommodations.show', $acc['id']) }}#chambres" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" class="h-4 w-4" /> {{ $acc['name'] }}</a>
        <h1 class="mt-3 font-title text-2xl font-extrabold md:text-3xl">{{ $room['name'] }}</h1>
        <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
            <span class="inline-flex items-center gap-1"><x-icon name="users" class="h-4 w-4" /> {{ $room['capacity'] }} personne(s) max.</span>
            @if (!empty($room['surface_area']))<span class="inline-flex items-center gap-1"><x-icon name="maximize" class="h-4 w-4" /> {{ $room['surface_area'] }} m²</span>@endif
            @if (!empty($room['bedding']))<span class="inline-flex items-center gap-1"><x-icon name="bed-double" class="h-4 w-4" /> {{ is_array($room['bedding']) ? implode(', ', $room['bedding']) : $room['bedding'] }}</span>@endif
            @if ($price > 0)<span class="inline-flex items-center gap-1 font-semibold text-gray-900 dark:text-white">{{ Fmt::price($price) }} <span class="font-normal text-gray-500">/ nuit</span></span>@endif
        </p>

        <div class="mt-5 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start lg:gap-8">
            {{-- Photos --}}
            <div class="min-w-0 lg:col-start-1 lg:row-start-1">
                @include('web.partials.gallery', ['images' => $room['images'] ?? [], 'alt' => $room['name'], 'demo' => \App\Support\DemoPhotos::room($acc, $room)])
            </div>

            {{-- Configuration du séjour : juste sous les photos sur mobile, colonne de droite sur ordinateur --}}
            <aside class="lg:sticky lg:top-20 lg:col-start-2 lg:row-span-2 lg:row-start-1">
                @if ($price > 0)
                    <section class="card p-4 sm:p-5" aria-labelledby="sejour-titre">
                        <h2 id="sejour-titre" class="section-title">Votre séjour</h2>

                        {{-- Dates --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between">
                                <button type="button" class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-100 disabled:opacity-30 dark:hover:bg-gray-800" @click="prevMonth()" :disabled="!canPrev" aria-label="Mois précédent"><x-icon name="chevron-left" class="h-5 w-5" /></button>
                                <p class="font-semibold capitalize" x-text="monthLabel"></p>
                                <button type="button" class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800" @click="nextMonth()" aria-label="Mois suivant"><x-icon name="chevron-right" class="h-5 w-5" /></button>
                            </div>
                            <div class="mt-2 grid grid-cols-7 text-center text-xs font-medium text-gray-500" aria-hidden="true">
                                <span>lu</span><span>ma</span><span>me</span><span>je</span><span>ve</span><span>sa</span><span>di</span>
                            </div>
                            <div class="mt-1 grid grid-cols-7 gap-y-1" role="grid">
                                <template x-for="d in days" :key="d.key">
                                    <div :class="d.inside ? 'bg-primary/10' : ''">
                                        <button type="button" x-show="d.iso" @click="pick(d.iso)" :disabled="d.disabled"
                                                :aria-label="d.iso && label(d.iso)" :aria-pressed="d.edge"
                                                class="h-11 w-full rounded-lg text-sm tabular-nums transition-colors"
                                                :class="d.edge ? 'bg-primary font-bold text-white' : (d.disabled ? (d.taken ? 'text-gray-300 line-through dark:text-gray-600' : 'text-gray-300 dark:text-gray-600') : 'hover:bg-primary/10')"
                                                x-text="d.n"></button>
                                    </div>
                                </template>
                            </div>
                            <p class="mt-2 flex items-center gap-1.5 text-xs text-gray-500"><span class="line-through">12</span> déjà réservé</p>

                            <div class="mt-3 rounded-lg bg-gray-50 px-3 py-2.5 text-sm dark:bg-gray-800/60">
                                <p x-show="hint" x-text="hint" class="text-gray-600 dark:text-gray-400"></p>
                                <div x-show="!hint" x-cloak class="flex items-center justify-between gap-2">
                                    <p><span x-text="checkIn && label(checkIn)"></span> → <span x-text="checkOut && label(checkOut)"></span> · <strong x-text="nights + (nights > 1 ? ' nuits' : ' nuit')"></strong></p>
                                    <button type="button" class="link shrink-0 text-xs" @click="clear()">Effacer</button>
                                </div>
                            </div>
                            <p x-show="error" x-cloak x-text="error" class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"></p>
                        </div>

                        {{-- Personnes (et chambres si plusieurs du même type) --}}
                        <div class="mt-4 space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
                            @if ($quantity > 1)
                                <div class="flex items-center justify-between gap-3">
                                    <div><p class="font-medium">Chambres</p><p class="text-xs text-gray-500">{{ $quantity }} disponibles de ce type</p></div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 disabled:opacity-30 dark:border-gray-700" @click="setRooms(rooms - 1)" :disabled="rooms <= 1" aria-label="Une chambre de moins"><x-icon name="minus" class="h-4 w-4" /></button>
                                        <span class="w-8 text-center font-semibold tabular-nums" x-text="rooms"></span>
                                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 disabled:opacity-30 dark:border-gray-700" @click="setRooms(rooms + 1)" :disabled="rooms >= {{ $quantity }}" aria-label="Une chambre de plus"><x-icon name="plus" class="h-4 w-4" /></button>
                                    </div>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3">
                                <div><p class="font-medium">Personnes</p><p class="text-xs text-gray-500" x-text="'Jusqu’à ' + maxGuests"></p></div>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 disabled:opacity-30 dark:border-gray-700" @click="guests = Math.max(1, guests - 1)" :disabled="guests <= 1" aria-label="Une personne de moins"><x-icon name="minus" class="h-4 w-4" /></button>
                                    <span class="w-8 text-center font-semibold tabular-nums" x-text="guests"></span>
                                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 disabled:opacity-30 dark:border-gray-700" @click="guests = Math.min(maxGuests, guests + 1)" :disabled="guests >= maxGuests" aria-label="Une personne de plus"><x-icon name="plus" class="h-4 w-4" /></button>
                                </div>
                            </div>
                        </div>

                        {{-- Prix --}}
                        <div class="mt-4 border-t border-gray-200 pt-4 text-sm dark:border-gray-800" x-show="quote" x-cloak>
                            <p class="flex justify-between gap-2"><span x-text="quote && (fcfa(quote.effective_price_per_night) + ' × ' + quote.nights + (quote.nights > 1 ? ' nuits' : ' nuit') + (rooms > 1 ? ' × ' + rooms + ' chambres' : ''))"></span><span class="tabular-nums" x-text="quote && fcfa(quote.effective_price_per_night * quote.nights * rooms)"></span></p>
                            <template x-if="quote && quote.long_stay_discount">
                                <p class="mt-1 flex justify-between gap-2 text-green-700"><span x-text="quote.long_stay_discount.label"></span><span class="tabular-nums" x-text="'− ' + fcfa(quote.long_stay_discount.discount_amount)"></span></p>
                            </template>
                            <p class="mt-2 flex justify-between gap-2 border-t border-gray-200 pt-2 text-base font-bold dark:border-gray-800"><span>Total</span><span class="tabular-nums" x-text="quote && fcfa(quote.total)"></span></p>
                            <template x-if="quote && quote.payment_options && quote.payment_options.options && quote.payment_options.options.full && quote.payment_options.options.full.discount_percent">
                                <p class="mt-2 rounded-lg bg-green-50 px-3 py-2 text-green-800" x-text="'Payez tout en ligne : −' + quote.payment_options.options.full.discount_percent + ' % (' + fcfa(quote.payment_options.options.full.amount) + ')'"></p>
                            </template>
                            <p class="mt-2 flex gap-2 text-gray-600 dark:text-gray-400" x-show="quote && quote.cancellation_policy_hours > 0"><x-icon name="calendar-x" class="mt-0.5 h-4 w-4 shrink-0" /> <span x-text="quote && ('Annulation gratuite jusqu’à ' + quote.cancellation_policy_hours + ' h avant l’arrivée.')"></span></p>
                        </div>
                        <p class="mt-4 text-sm text-gray-500" x-show="loadingQuote" x-cloak>Calcul du prix…</p>

                        <a :href="ready ? continueUrl : null" :aria-disabled="!ready" class="btn-primary mt-5 hidden w-full lg:flex" :class="!ready && 'pointer-events-none opacity-50'">Continuer <x-icon name="arrow-right" class="h-4 w-4" /></a>
                    </section>
                @else
                    <section class="card p-4 sm:p-5">
                        <h2 class="section-title">Tarif sur demande</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Cette chambre ne se réserve pas encore en ligne. Écrivez-nous pour connaître le tarif et les disponibilités.</p>
                        @if ($wa)
                            <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Bonjour, je souhaite connaître le tarif et les disponibilités : ' . $room['name'] . ' — ' . $acc['name'] . '.') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-4 w-full"><x-icon name="message-circle" class="h-4 w-4" /> Demander le tarif</a>
                        @else
                            <a href="{{ route('contact') }}" class="btn-primary mt-4 w-full"><x-icon name="mail" class="h-4 w-4" /> Demander le tarif</a>
                        @endif
                    </section>
                @endif
            </aside>

            {{-- Description et équipements --}}
            <div class="min-w-0 space-y-8 lg:col-start-1 lg:row-start-2">
                @if (!empty($room['description']))
                    <section>
                        <h2 class="section-title">Description</h2>
                        <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-700 dark:text-gray-300">{{ $room['description'] }}</p>
                    </section>
                @endif
                @if ($all)
                    <section>
                        <h2 class="section-title">Équipements</h2>
                        <ul class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($all as $a)
                                <li class="flex items-center gap-2 text-sm"><x-icon name="check" class="h-4 w-4 text-primary" /> {{ Fmt::amenity(is_array($a) ? ($a['name'] ?? '') : $a) }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>

        @if ($price > 0)
            {{-- Mobile : total et « Continuer » toujours sous le pouce. --}}
            <div class="sticky bottom-0 z-30 -mx-4 mt-8 flex items-center justify-between gap-3 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden dark:border-gray-800 dark:bg-gray-950/95">
                <p class="min-w-0 leading-tight">
                    <template x-if="ready">
                        <span><span class="block text-xs text-gray-500" x-text="nights + (nights > 1 ? ' nuits' : ' nuit') + ' · ' + guests + (guests > 1 ? ' pers.' : ' pers.')"></span><span class="font-bold tabular-nums" x-text="fcfa(quote.total)"></span></span>
                    </template>
                    <template x-if="!ready">
                        <span class="text-sm text-gray-600 dark:text-gray-400"><span class="font-bold text-gray-900 dark:text-white">{{ Fmt::price($price) }}</span> / nuit<span class="block text-xs">Choisissez vos dates</span></span>
                    </template>
                </p>
                <a :href="ready ? continueUrl : '#sejour-titre'" class="btn-primary shrink-0" :class="!ready && 'opacity-60'"><span x-text="ready ? 'Continuer' : 'Choisir les dates'"></span> <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
        @endif
    </div>
</x-layouts.public>
