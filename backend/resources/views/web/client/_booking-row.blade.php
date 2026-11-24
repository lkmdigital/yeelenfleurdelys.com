@php
    use App\Support\Fmt;
    [$label, $cls] = Fmt::bookingStatus($b['status']);
    $ref = $b['access_token'] ?? $b['id'];
    $img = Fmt::coverOrNull($b['accommodation'] ?? []) ?: \App\Support\DemoPhotos::for($b['accommodation'] ?? [], 300);
@endphp
{{-- Mobile : vignette à gauche, infos à droite (carte compacte). --}}
<a href="{{ route('bookings.show', $ref) }}" class="card flex items-start gap-3 p-4 transition hover:border-primary/50 sm:items-center sm:gap-4 sm:p-5">
    <span class="block h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-primary sm:w-28">
        <img src="{{ $img }}" alt="" loading="lazy" class="h-full w-full object-cover">
    </span>
    <span class="min-w-0 flex-1">
        <span class="flex flex-wrap items-center gap-x-2 gap-y-1"><span class="font-semibold">{{ $b['accommodation']['name'] ?? 'Établissement' }}</span><span class="badge {{ $cls }}">{{ $label }}</span></span>
        <span class="mt-1 block text-sm text-gray-600 dark:text-gray-400">{{ $b['room']['name'] ?? '' }} · {{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }} · {{ $b['guests'] }} pers.</span>
        <span class="mt-2 flex items-baseline justify-between gap-2 sm:hidden">
            <span class="text-xs text-gray-500">N° {{ $b['booking_number'] ?? $b['id'] }}</span>
            <span class="text-right"><span class="font-bold">{{ Fmt::price($b['total_price']) }}</span> <span class="text-xs text-gray-500">· {{ Fmt::paymentStatus($b['payment_status'] ?? null) }}</span></span>
        </span>
        <span class="mt-1 hidden text-xs text-gray-500 sm:block">N° {{ $b['booking_number'] ?? $b['id'] }}</span>
    </span>
    <span class="hidden text-right sm:block">
        <span class="block font-bold">{{ Fmt::price($b['total_price']) }}</span>
        <span class="text-xs text-gray-500">{{ Fmt::paymentStatus($b['payment_status'] ?? null) }}</span>
    </span>
</a>
