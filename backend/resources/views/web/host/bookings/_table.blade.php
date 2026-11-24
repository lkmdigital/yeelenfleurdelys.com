{{-- Tableau des réservations du gérant. --}}
@php use App\Support\Fmt; @endphp
@if ($bookings)
    {{-- Mobile : une carte par réservation (le tableau ne tient pas en largeur). --}}
    <ul class="space-y-3 md:hidden">
        @foreach ($bookings as $b)
            <li class="card p-4">
                <a href="{{ route('host.bookings.show', $b['id']) }}" class="block">
                    <span class="flex items-start justify-between gap-3">
                        <span class="min-w-0">
                            <span class="block font-semibold">{{ ($b['traveler_name'] ?? null) ?: ($b['user']['name'] ?? '—') }}</span>
                            <span class="block text-xs text-gray-500">{{ $b['booking_number'] ?? '#' . $b['id'] }} · {{ $b['room']['name'] ?? 'Établissement' }}@if (($b['rooms_quantity'] ?? 1) > 1) × {{ $b['rooms_quantity'] }}@endif</span>
                        </span>
                        <x-status :status="$b['status']" />
                    </span>
                    <span class="mt-2 block text-sm">{{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }} <span class="text-gray-500">· {{ Fmt::nights($b['check_in'], $b['check_out']) }} nuit(s) · {{ $b['guests'] }} pers.</span></span>
                    <span class="mt-1 flex items-baseline justify-between gap-2 text-sm">
                        <span class="font-bold tabular-nums">{{ Fmt::price($b['total_price']) }} <span class="text-xs font-normal text-gray-500">· {{ Fmt::paymentStatus($b['payment_status']) }}</span></span>
                        @if (empty($b['user']['phone']) || $b['status'] === 'awaiting_host_confirmation')<x-icon name="chevron-right" class="h-4 w-4 text-gray-400" />@endif
                    </span>
                </a>
                @if (!empty($b['user']['phone']) || $b['status'] === 'awaiting_host_confirmation')
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-gray-200 pt-3 dark:border-gray-800">
                        @if ($b['status'] === 'awaiting_host_confirmation')
                            <form method="POST" action="{{ route('host.bookings.act', [$b['id'], 'approve']) }}">@csrf<button class="btn-primary w-full" type="submit">Confirmer</button></form>
                            <a href="{{ route('host.bookings.show', $b['id']) }}#refuser" class="btn-secondary">Refuser</a>
                        @else
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $b['user']['phone']) }}" class="btn-secondary"><x-icon name="phone" class="h-4 w-4" /> Appeler</a>
                            <a href="{{ route('host.bookings.show', $b['id']) }}" class="btn-ghost">Ouvrir <x-icon name="chevron-right" /></a>
                        @endif
                    </div>
                @endif
            </li>
        @endforeach
    </ul>

    <div class="table-wrap hidden md:block">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3">Réservation</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Séjour</th>
                    <th class="px-4 py-3 text-right">Montant</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($bookings as $b)
                    <tr class="align-top">
                        <td class="px-4 py-3">
                            <a href="{{ route('host.bookings.show', $b['id']) }}" class="link">{{ $b['booking_number'] ?? '#' . $b['id'] }}</a>
                            <span class="block text-xs text-gray-500">{{ $b['room']['name'] ?? 'Établissement' }}@if (($b['rooms_quantity'] ?? 1) > 1) × {{ $b['rooms_quantity'] }}@endif</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ ($b['traveler_name'] ?? null) ?: ($b['user']['name'] ?? '—') }}</span>
                            @if (!empty($b['user']['phone']))<span class="block text-xs text-gray-500">{{ $b['user']['phone'] }}</span>@endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            {{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }}
                            <span class="block text-xs text-gray-500">{{ Fmt::nights($b['check_in'], $b['check_out']) }} nuit(s) · {{ $b['guests'] }} pers.</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                            {{ Fmt::price($b['total_price']) }}
                            <span class="block text-xs text-gray-500">{{ Fmt::paymentStatus($b['payment_status']) }}</span>
                        </td>
                        <td class="px-4 py-3"><x-status :status="$b['status']" /></td>
                        <td class="px-4 py-3 text-right">
                            @if ($b['status'] === 'awaiting_host_confirmation')
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('host.bookings.act', [$b['id'], 'approve']) }}">@csrf<button class="btn-primary btn-sm" type="submit">Confirmer</button></form>
                                    <a href="{{ route('host.bookings.show', $b['id']) }}#refuser" class="btn-secondary btn-sm">Refuser</a>
                                </div>
                            @else
                                <a href="{{ route('host.bookings.show', $b['id']) }}" class="btn-ghost btn-sm">Ouvrir <x-icon name="chevron-right" /></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <x-empty :title="$emptyTitle ?? 'Aucune réservation'" icon="calendar">{{ $emptyText ?? '' }}</x-empty>
@endif
