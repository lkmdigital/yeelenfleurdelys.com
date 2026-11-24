@props(['status', 'label' => null])
@php [$text, $cls] = \App\Support\Fmt::bookingStatus($status, $label); @endphp
<span {{ $attributes->merge(['class' => "badge $cls"]) }}>{{ $text }}</span>
