@php
    $start = $month->copy()->startOfMonth();
    $n = $month->daysInMonth;
    $today = now()->toDateString();
@endphp
<x-layouts.panel space="host" title="Calendrier">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Calendrier</h1>
        <div class="flex items-center gap-2">
            <a class="btn-secondary btn-sm" href="?month={{ $month->copy()->subMonth()->format('Y-m') }}" aria-label="Mois précédent"><x-icon name="chevron-left" /></a>
            <span class="min-w-[140px] text-center font-semibold capitalize">{{ $month->locale('fr')->isoFormat('MMMM YYYY') }}</span>
            <a class="btn-secondary btn-sm" href="?month={{ $month->copy()->addMonth()->format('Y-m') }}" aria-label="Mois suivant"><x-icon name="chevron-right" /></a>
        </div>
    </div>
    @if ($rooms)
        <div class="table-wrap">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="sticky left-0 z-10 min-w-[150px] bg-gray-50 px-3 py-2 text-left text-sm dark:bg-gray-800">Chambre</th>
                        @for ($i = 0; $i < $n; $i++)
                            @php $d = $start->copy()->addDays($i); @endphp
                            <th class="min-w-[28px] px-0.5 py-2 text-center font-medium {{ $d->toDateString() === $today ? 'text-primary' : 'text-gray-500' }}">{{ mb_substr($d->locale('fr')->isoFormat('dd'), 0, 2) }}<span class="block text-sm text-gray-900 dark:text-gray-100">{{ $d->day }}</span></th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($rooms as $r)
                        @php $qty = max(1, (int) ($r['quantity'] ?? 1)); @endphp
                        <tr>
                            <th class="sticky left-0 z-10 bg-white px-3 py-2 text-left text-sm font-medium dark:bg-gray-900">
                                <a href="{{ route('host.rooms.calendar', ['room' => $r['id'], 'month' => $month->format('Y-m')]) }}" class="hover:underline">{{ $r['name'] }}</a>
                                <span class="block text-xs font-normal text-gray-500">{{ $qty }} chambre(s)</span>
                            </th>
                            @for ($i = 0; $i < $n; $i++)
                                @php
                                    $date = $start->copy()->addDays($i)->toDateString();
                                    $st = $grid[$r['id']][$date]['status'] ?? 'available';
                                    $taken = $sold[$r['id']][$date] ?? 0;
                                    $left = max(0, $qty - $taken);
                                    $cls = match (true) {
                                        in_array($st, ['blocked', 'maintenance'], true) => 'bg-gray-200 text-gray-500 dark:bg-gray-800',
                                        $left === 0 || $st === 'occupied' => 'bg-primary text-white',
                                        $taken > 0 => 'bg-primary/20',
                                        default => '',
                                    };
                                    $label = in_array($st, ['blocked', 'maintenance'], true) ? 'Fermée' : ($left === 0 || $st === 'occupied' ? 'Complet' : "$left libre(s)");
                                @endphp
                                <td class="h-10 border-l border-gray-100 text-center tabular-nums dark:border-gray-800 {{ $cls }}" title="{{ $date }} : {{ $label }}">
                                    {{ in_array($st, ['blocked', 'maintenance'], true) ? '×' : ($taken ? $taken : '') }}
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-gray-300"></span>Libre</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-primary/20"></span>Partiellement réservée (nombre de chambres prises)</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-primary"></span>Complet</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-gray-200"></span>Fermée à la vente</span>
        </div>
        <p class="mt-2 text-sm text-gray-500">Cliquez sur une chambre pour fermer des dates ou changer son prix.</p>
    @else
        <x-empty title="Aucune chambre" icon="calendar-days"><x-slot:action><a href="{{ route('host.rooms.create') }}" class="btn-primary">Ajouter une chambre</a></x-slot:action></x-empty>
    @endif
</x-layouts.panel>
