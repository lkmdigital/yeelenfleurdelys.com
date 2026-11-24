@php
    use App\Support\Fmt;
    $price = $room['effective_price_per_night'] ?? $room['price_per_night'];
    $hasDates = $search['check_in'] && $search['check_out'];
@endphp
<x-layouts.public :title="$room['name'] . ' — ' . $acc['name']">
    <div class="mx-auto max-w-6xl px-4 py-8">
        <a href="{{ route('accommodations.show', array_filter(['id' => $acc['id'], 'check_in' => $search['check_in'], 'check_out' => $search['check_out'], 'guests' => $search['guests']])) }}#chambres" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" class="h-4 w-4" /> {{ $acc['name'] }}</a>
        <h1 class="mt-3 font-title text-2xl font-extrabold md:text-3xl">{{ $room['name'] }}</h1>
        <p class="mt-1 flex flex-wrap gap-x-4 text-gray-600 dark:text-gray-400">
            <span class="inline-flex items-center gap-1"><x-icon name="users" class="h-4 w-4" /> {{ $room['capacity'] }} personne(s) max.</span>
            @if (!empty($room['surface_area']))<span class="inline-flex items-center gap-1"><x-icon name="maximize" class="h-4 w-4" /> {{ $room['surface_area'] }} m²</span>@endif
            @if (!empty($room['bedding']))<span class="inline-flex items-center gap-1"><x-icon name="bed-double" class="h-4 w-4" /> {{ is_array($room['bedding']) ? implode(', ', $room['bedding']) : $room['bedding'] }}</span>@endif
        </p>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_340px] lg:items-start">
            <div class="min-w-0 space-y-8">
                @include('web.partials.gallery', ['images' => $room['images'] ?? [], 'alt' => $room['name'], 'demo' => \App\Support\DemoPhotos::room($acc, $room)])
                @if (!empty($room['description']))
                    <section>
                        <h2 class="section-title">Description</h2>
                        <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-700 dark:text-gray-300">{{ $room['description'] }}</p>
                    </section>
                @endif
                @php $all = array_merge($room['amenities'] ?? [], $room['basic_amenities'] ?? [], $room['premium_amenities'] ?? []); @endphp
                @if ($all)
                    <section>
                        <h2 class="section-title">Équipements</h2>
                        <ul class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach (array_unique($all) as $a)
                                <li class="flex items-center gap-2 text-sm"><x-icon name="check" class="h-4 w-4 text-primary" /> {{ Fmt::amenity(is_array($a) ? ($a['name'] ?? '') : $a) }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="card lg:sticky lg:top-20">
                @if ($price > 0)
                    <p class="text-2xl font-bold">{{ Fmt::price($price) }} <span class="text-sm font-normal text-gray-500">/ nuit</span></p>
                @else
                    <p class="font-semibold">Tarif sur demande</p>
                @endif
                <form method="GET" action="{{ route('bookings.new') }}" class="mt-4 space-y-3">
                    <input type="hidden" name="room_id" value="{{ $room['id'] }}">
                    <x-input name="check_in" type="date" label="Arrivée" :value="$search['check_in']" :min="now()->toDateString()" required />
                    <x-input name="check_out" type="date" label="Départ" :value="$search['check_out']" :min="now()->addDay()->toDateString()" required />
                    <x-input name="guests" type="number" label="Voyageurs" :value="$search['guests']" min="1" :max="$room['capacity']" />
                    @if ($quote)
                        <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                            <p class="flex justify-between"><span>{{ $quote['nights'] }} nuit(s)</span><strong>{{ Fmt::price($quote['total']) }}</strong></p>
                        </div>
                    @endif
                    <button type="submit" class="btn-primary w-full" @disabled($price <= 0)>Réserver cette chambre</button>
                </form>
            </aside>
        </div>
    </div>
</x-layouts.public>
