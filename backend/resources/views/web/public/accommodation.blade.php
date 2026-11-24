@php
    use App\Support\Fmt;
    $price = $acc['effective_price_per_night'] ?? $acc['price_per_night'] ?? 0;
    $minRoom = collect($rooms)->map(fn ($r) => $r['effective_price_per_night'] ?? $r['price_per_night'])->filter(fn ($p) => $p > 0)->min();
    $from = $minRoom ?: $price;
    $hours = $acc['cancellation_policy_hours'] ?? 48;
    $policy = $hours == 0 ? 'Non remboursable : aucune annulation gratuite.' : "Annulation gratuite jusqu'à {$hours} h avant l'arrivée.";
    $wa = $acc['whatsapp'] ? preg_replace('/\D/', '', $acc['whatsapp']) : null;
    if ($wa && strlen($wa) === 10) $wa = '225' . $wa;
    $services = \App\Support\EstablishmentServices::for($acc);
    $waText = fn (string $m) => 'https://wa.me/' . $wa . '?text=' . rawurlencode($m);
@endphp
<x-layouts.public :title="$acc['name']" :description="\Illuminate\Support\Str::limit($acc['description'] ?? '', 155)">
    <div class="mx-auto max-w-7xl px-4 pt-6 md:py-8">
        <a href="{{ route('home') }}#etablissements" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" class="h-4 w-4" /> Tous nos établissements</a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="badge bg-primary/10 text-primary">{{ Fmt::type($acc['type']) }}@if (!empty($acc['star_rating'])) · {{ $acc['star_rating'] }} étoiles @endif</p>
                <h1 class="mt-2 font-title text-2xl font-extrabold md:text-3xl">{{ $acc['name'] }}</h1>
                <p class="mt-1 flex items-center gap-1.5 text-gray-600 dark:text-gray-400"><x-icon name="map-pin" class="h-4 w-4 text-primary" /> {{ $acc['address'] ?: $acc['city'] }}</p>
                @if (($acc['total_reviews'] ?? 0) > 0)
                    <p class="mt-1 flex items-center gap-2 text-sm">@include('web.partials.stars', ['value' => (float) $acc['rating']]) <span>{{ number_format((float) $acc['rating'], 1, ',', ' ') }} · {{ $acc['total_reviews'] }} avis</span></p>
                @endif
            </div>
            @auth
                <form method="POST" action="{{ route('favorites.toggle', $acc['id']) }}">@csrf
                    <button class="btn-secondary" type="submit"><x-icon name="heart" class="h-5 w-5 {{ $favorite ? 'fill-current text-red-600' : '' }}" /> {{ $favorite ? 'Dans vos favoris' : 'Ajouter aux favoris' }}</button>
                </form>
            @endauth
        </div>

        <div class="mt-6">@include('web.partials.gallery', ['images' => $acc['images'] ?? [], 'alt' => $acc['name'], 'demo' => \App\Support\DemoPhotos::gallery($acc)])</div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_340px] lg:items-start">
            <div class="min-w-0 space-y-8">
                @if (!empty($acc['description']))
                    <section>
                        <h2 class="section-title">Présentation</h2>
                        <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-700 dark:text-gray-300">{{ $acc['description'] }}</p>
                    </section>
                @endif


                <section id="chambres" class="scroll-mt-20">
                    <h2 class="section-title">Nos chambres</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Choisissez votre chambre : vous indiquerez ensuite vos dates et le nombre de personnes.</p>

                    @if (!$rooms && $onRequest)
                        <div class="card mt-4 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($onRequest as $room)
                                @include('web.partials.room-request-card', ['room' => $room])
                            @endforeach
                        </div>
                    @elseif (!$rooms)
                        <x-empty class="mt-4" title="Aucune chambre n'est encore proposée en ligne." icon="bed-double">
                            Contactez directement l'établissement pour connaître ses disponibilités et ses tarifs.
                        </x-empty>
                    @else
                        <div class="card mt-4 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($rooms as $room)
                                @include('web.partials.room-card', ['room' => $room, 'search' => $search])
                            @endforeach
                            @foreach ($onRequest as $room)
                                @include('web.partials.room-request-card', ['room' => $room])
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($services)
                    <section id="services" class="scroll-mt-20">
                        <h2 class="section-title">Nos services</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Sur devis : écrivez-nous, nous vous répondons rapidement.</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($services as $sv)
                                <div class="card flex flex-col p-4">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><x-icon :name="$sv['icon']" class="h-5 w-5" /></span>
                                        <div class="min-w-0">
                                            <h3 class="font-semibold">{{ $sv['title'] }}</h3>
                                            <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">{{ $sv['text'] }}</p>
                                        </div>
                                    </div>
                                    @if ($wa)
                                        <a href="{{ $waText($sv['message']) }}" target="_blank" rel="noopener noreferrer" class="btn-secondary mt-4 w-full"><x-icon name="message-circle" class="h-4 w-4" /> Demander un devis</a>
                                    @else
                                        <a href="{{ route('contact') }}" class="btn-secondary mt-4 w-full"><x-icon name="mail" class="h-4 w-4" /> Demander un devis</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (!empty($acc['amenities']))
                    <section>
                        <h2 class="section-title">Équipements</h2>
                        <ul class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($acc['amenities'] as $a)
                                <li class="flex items-center gap-2 text-sm"><x-icon name="check" class="h-4 w-4 text-primary" /> {{ Fmt::amenity($a) }}</li>
                            @endforeach
                            @if ($acc['reception_24h'] ?? false)<li class="flex items-center gap-2 text-sm"><x-icon name="check" class="h-4 w-4 text-primary" /> Réception 24 h/24</li>@endif
                            @if ($acc['shuttle_service'] ?? false)<li class="flex items-center gap-2 text-sm"><x-icon name="check" class="h-4 w-4 text-primary" /> Navette</li>@endif
                            @if ($acc['laundry'] ?? false)<li class="flex items-center gap-2 text-sm"><x-icon name="check" class="h-4 w-4 text-primary" /> Blanchisserie</li>@endif
                        </ul>
                    </section>
                @endif

                @if ($quote && ($quote['variants']['enabled'] ?? false))
                    <section>
                        <h2 class="section-title">Plans tarifaires</h2>
                        <div class="mt-3 space-y-2">
                            @foreach (['base', 'non_refundable', 'modifiable', 'long_stay'] as $k)
                                @if (!empty($quote['variants'][$k]))
                                    @php $v = $quote['variants'][$k]; @endphp
                                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                                        <span>{{ $v['label'] }}</span>
                                        <span class="font-semibold">{{ Fmt::price($v['price_per_night']) }} / nuit @if (!empty($v['adjustment_label']))<span class="text-sm font-normal text-green-700">({{ $v['adjustment_label'] }})</span>@endif</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                <section>
                    <h2 class="section-title">Conditions</h2>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <div class="card">
                            <x-icon name="clock" class="h-5 w-5 text-primary" />
                            <h3 class="mt-2 font-semibold">Horaires</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Arrivée à partir de {{ Fmt::time($acc['check_in_time'] ?? '14:00') }}<br>Départ avant {{ Fmt::time($acc['check_out_time'] ?? '12:00') }}</p>
                        </div>
                        <div class="card">
                            <x-icon name="calendar-x" class="h-5 w-5 text-primary" />
                            <h3 class="mt-2 font-semibold">Annulation</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $policy }}</p>
                        </div>
                        <div class="card">
                            <x-icon name="shield-check" class="h-5 w-5 text-primary" />
                            <h3 class="mt-2 font-semibold">Paiement</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $acc['guarantee']['description'] ?? 'Paiement en ligne sécurisé : Mobile Money ou carte bancaire.' }}</p>
                        </div>
                    </div>
                    @if (!empty($acc['special_conditions']))
                        <p class="mt-3 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $acc['special_conditions'] }}</p>
                    @endif
                </section>

                <section>
                    <h2 class="section-title">Avis des clients</h2>
                    @forelse ($reviews as $r)
                        <article class="mt-4 border-b border-gray-200 pb-4 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold">{{ $r['user']['first_name'] ?? $r['user']['name'] ?? $r['guest_name'] ?? 'Client' }}</p>
                                <p class="text-xs text-gray-500">{{ Fmt::date($r['created_at'] ?? null, 'F Y') }}</p>
                            </div>
                            <div class="mt-1">@include('web.partials.stars', ['value' => (float) ($r['rating'] ?? 0)])</div>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $r['comment'] }}</p>
                            @if (!empty($r['host_response']))
                                <p class="mt-2 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800"><strong>Réponse de l'établissement :</strong> {{ $r['host_response'] }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Pas encore d'avis publié. Les clients reçoivent un lien pour donner leur avis après leur séjour.</p>
                    @endforelse
                </section>

                @if (!empty($acc['latitude']) && !empty($acc['longitude']))
                    <section>
                        <h2 class="section-title">Localisation</h2>
                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $acc['address'] }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $acc['latitude'] }},{{ $acc['longitude'] }}" target="_blank" rel="noopener noreferrer" class="btn-secondary mt-3"><x-icon name="map" /> Voir sur la carte</a>
                    </section>
                @endif
            </div>

            <aside class="space-y-4 lg:sticky lg:top-20">
                <div class="card hidden lg:block">
                    @if ($from > 0)
                        <p class="text-sm text-gray-600 dark:text-gray-400">À partir de</p>
                        <p class="text-2xl font-bold">{{ Fmt::price($from) }} <span class="text-sm font-normal text-gray-500">/ nuit</span></p>
                    @else
                        <p class="font-semibold">Tarifs sur demande</p>
                    @endif
                    @if (!empty($acc['guarantee']['enabled']))
                        <p class="mt-3 flex gap-2 text-sm text-gray-700 dark:text-gray-300"><x-icon name="shield-check" class="mt-0.5 h-4 w-4 shrink-0 text-primary" /> Garantie : {{ $acc['guarantee']['summary'] }} payée en ligne, le solde à l'arrivée.</p>
                    @endif
                    <a href="#chambres" class="btn-primary mt-4 w-full">Voir les chambres</a>
                </div>
                <div class="card">
                    <h2 class="font-semibold">Contacter l'établissement</h2>
                    @if ($acc['whatsapp'])
                        {{-- Sur téléphone, appeler et WhatsApp sont les actions principales : vrais boutons, faciles à toucher. --}}
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="tel:+{{ $wa }}" class="btn-secondary"><x-icon name="phone" class="h-4 w-4" /> Appeler</a>
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" class="btn-primary"><x-icon name="message-circle" class="h-4 w-4" /> WhatsApp</a>
                        </div>
                        <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">{{ $acc['whatsapp'] }}</p>
                    @else
                        <a href="{{ route('contact') }}" class="mt-3 flex items-center gap-2 text-sm hover:text-primary"><x-icon name="mail" class="h-4 w-4 text-primary" /> Nous écrire</a>
                    @endif
                </div>
            </aside>
        </div>

        {{-- Mobile : rappel du prix et accès direct aux chambres, toujours sous le pouce. --}}
        <div class="sticky bottom-0 z-30 -mx-4 mt-8 flex items-center justify-between gap-3 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden dark:border-gray-800 dark:bg-gray-950/95">
            <p class="min-w-0 leading-tight">
                @if ($from > 0)
                    <span class="block text-xs text-gray-500">À partir de</span>
                    <span class="font-bold">{{ Fmt::price($from) }}</span> <span class="text-xs text-gray-500">/ nuit</span>
                @else
                    <span class="text-sm font-semibold">Tarifs sur demande</span>
                @endif
            </p>
            <a href="#chambres" class="btn-primary shrink-0">Voir les chambres</a>
        </div>
    </div>
</x-layouts.public>
