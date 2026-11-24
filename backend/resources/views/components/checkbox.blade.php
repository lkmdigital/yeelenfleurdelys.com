@props(['name', 'label', 'checked' => false, 'value' => '1', 'hint' => null])
@php $key = str_replace(['[', ']'], ['.', ''], $name); @endphp
<label {{ $attributes->only('class')->merge(['class' => 'flex items-start gap-2.5 text-sm']) }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked(old($key, $checked)) {{ $attributes->except('class') }}
        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary accent-[rgb(var(--brand))] focus:ring-primary">
    <span>{{ $label }} @if ($hint)<span class="block text-xs text-gray-500">{{ $hint }}</span>@endif</span>
</label>
