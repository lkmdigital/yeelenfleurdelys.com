{{-- Récapitulatif d'une réservation (tableau d'API $b). --}}
@php
    use App\Support\Fmt;
    $acc = $b['accommodation'] ?? [];
@endphp
<div class="card space-y-4">
    <div class="flex gap-3">
        @if ($img = Fmt::coverOrNull($acc))<img src="{{ $img }}" alt="" class="h-16 w-20 rounded-lg object-cover">@endif
        <div class="min-w-0">
            <p class="font-semibold">{{ $acc['name'] ?? 'Établissement' }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $b['room']['name'] ?? '' }}@if (($b['rooms_quantity'] ?? 1) > 1) × {{ $b['rooms_quantity'] }}@endif</p>
        </div>
    </div>
    <dl class="space-y-1.5 border-t border-gray-200 pt-4 text-sm dark:border-gray-800">
        <div class="flex justify-between gap-3"><dt>Numéro</dt><dd class="font-mono font-medium">{{ $b['booking_number'] ?? ('#' . $b['id']) }}</dd></div>
        <div class="flex justify-between gap-3"><dt>Arrivée</dt><dd class="font-medium">{{ Fmt::dateLong($b['check_in']) }}@if (!empty($acc['check_in_time'])), dès {{ Fmt::time($acc['check_in_time']) }}@endif</dd></div>
        <div class="flex justify-between gap-3"><dt>Départ</dt><dd class="font-medium">{{ Fmt::dateLong($b['check_out']) }}@if (!empty($acc['check_out_time'])), avant {{ Fmt::time($acc['check_out_time']) }}@endif</dd></div>
        <div class="flex justify-between gap-3"><dt>Durée</dt><dd>{{ Fmt::nights($b['check_in'], $b['check_out']) }} nuit(s) · {{ $b['guests'] }} voyageur(s)</dd></div>
        <div class="flex justify-between gap-3 border-t border-gray-200 pt-2 text-base font-bold dark:border-gray-800"><dt>Total</dt><dd>{{ Fmt::price($b['total_price']) }}</dd></div>
        @if ((float) ($b['amount_paid'] ?? 0) > 0)
            <div class="flex justify-between gap-3 text-green-700"><dt>Déjà payé</dt><dd>{{ Fmt::price($b['amount_paid']) }}</dd></div>
            @if ((float) $b['total_price'] - (float) $b['amount_paid'] > 0)
                <div class="flex justify-between gap-3"><dt>Reste à régler à l'arrivée</dt><dd>{{ Fmt::price((float) $b['total_price'] - (float) $b['amount_paid']) }}</dd></div>
            @endif
        @endif
    </dl>
</div>
