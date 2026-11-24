@php
    use App\Support\Fmt; use App\Http\Controllers\Web\Host\RoomController as R;
    $start = $month->copy()->startOfMonth();
    $offset = ($start->dayOfWeekIso + 6) % 7;
    $colors = ['available' => 'bg-white dark:bg-gray-900', 'blocked' => 'bg-gray-200 text-gray-500 dark:bg-gray-800', 'maintenance' => 'bg-orange-100 dark:bg-orange-900/30', 'occupied' => 'bg-primary/15'];
    $today = now()->toDateString();
@endphp
<x-layouts.panel space="host" :title="'Calendrier · ' . $room['name']">
    <a href="{{ route('host.rooms') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Chambres</a>
    <h1 class="page-title mb-4">{{ $room['name'] }}</h1>
    @include('web.host.rooms._tabs')

    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <section class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <a class="btn-ghost btn-sm" href="?month={{ $month->copy()->subMonth()->format('Y-m') }}" aria-label="Mois précédent"><x-icon name="chevron-left" /></a>
                <h2 class="section-title capitalize">{{ $month->locale('fr')->isoFormat('MMMM YYYY') }}</h2>
                <a class="btn-ghost btn-sm" href="?month={{ $month->copy()->addMonth()->format('Y-m') }}" aria-label="Mois suivant"><x-icon name="chevron-right" /></a>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-500">
                @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $d)<div class="py-1">{{ $d }}</div>@endforeach
            </div>
            <div class="grid grid-cols-7 gap-1">
                @for ($i = 0; $i < $offset; $i++)<div></div>@endfor
                @for ($d = $start->copy(); $d->month === $month->month; $d->addDay())
                    @php $day = $days[$d->toDateString()] ?? null; $st = $day['status'] ?? 'available'; @endphp
                    <div class="min-h-[64px] rounded-md border border-gray-200 p-1.5 text-left text-xs dark:border-gray-800 {{ $colors[$st] ?? '' }} {{ $d->toDateString() === $today ? 'ring-2 ring-primary' : '' }} {{ $d->toDateString() < $today ? 'opacity-50' : '' }}" title="{{ R::AVAILABILITY[$st] ?? $st }}">
                        <span class="font-semibold">{{ $d->day }}</span>
                        @if ($day)<span class="mt-1 block tabular-nums {{ $day['price_override'] ? 'font-semibold text-primary' : 'text-gray-500' }}">{{ number_format($day['price'], 0, ',', ' ') }}</span>@endif
                        @if ($st !== 'available')<span class="block truncate">{{ R::AVAILABILITY[$st] ?? $st }}</span>@endif
                    </div>
                @endfor
            </div>
            <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-600 dark:text-gray-400">
                @foreach (R::AVAILABILITY as $k => $l)<span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-gray-300 {{ $colors[$k] }}"></span>{{ $l }}</span>@endforeach
                <span class="inline-flex items-center gap-1.5"><span class="font-semibold text-primary">12 000</span> prix modifié</span>
            </div>
        </section>

        <div class="space-y-6">
            <form method="POST" action="{{ route('host.rooms.calendar.update', $room['id']) }}" class="card space-y-3">
                @csrf
                <h2 class="section-title">Modifier des dates</h2>
                <div class="grid grid-cols-2 gap-3">
                    <x-input name="start_date" type="date" label="Du" :min="$today" required />
                    <x-input name="end_date" type="date" label="Au" :min="$today" required />
                </div>
                <x-select name="status" label="État" :options="array_diff_key(R::AVAILABILITY, ['occupied' => 1])" value="available" />
                <x-input name="price_override" type="number" min="0" step="500" label="Prix par nuit (FCFA)" hint="Vide = prix habituel." />
                <button class="btn-primary w-full" type="submit">Appliquer</button>
            </form>

            <section class="card">
                <h2 class="section-title">Tarifs par période</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Prix habituel : {{ Fmt::price($room['price_per_night']) }}. Un tarif de période le remplace sur les dates choisies (fêtes, vacances…).</p>
                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($periods as $p)
                        <li class="flex items-center justify-between gap-2 py-2">
                            <span><span class="font-medium">{{ $p['label'] ?: 'Période' }}</span><span class="block text-xs text-gray-500">{{ Fmt::date($p['start_date']) }} → {{ Fmt::date($p['end_date']) }} · {{ Fmt::price($p['price_per_night']) }}</span></span>
                            <form method="POST" action="{{ route('host.rooms.periods.destroy', [$room['id'], $p['id']]) }}">@csrf @method('DELETE')<button class="btn-ghost btn-sm px-2 text-red-700" type="submit" aria-label="Supprimer ce tarif"><x-icon name="trash-2" /></button></form>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">Aucun tarif de période.</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('host.rooms.periods.store', $room['id']) }}" class="mt-3 space-y-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                    @csrf
                    <x-input name="label" label="Nom" placeholder="Fêtes de fin d’année" id="p_label" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-input name="start_date" type="date" label="Du" id="p_start" required />
                        <x-input name="end_date" type="date" label="Au" id="p_end" required />
                    </div>
                    <x-input name="price_per_night" type="number" min="0" step="500" label="Prix par nuit (FCFA)" id="p_price" required />
                    <button class="btn-secondary w-full" type="submit">Ajouter ce tarif</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.panel>
