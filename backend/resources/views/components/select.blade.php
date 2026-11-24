@props(['name', 'label' => null, 'options' => [], 'value' => null, 'hint' => null, 'required' => false, 'placeholder' => null, 'id' => null])
@php
    $id = $id ?? 'f_' . str_replace(['[', ']', '.'], '_', $name);
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $err = $errors->first($key);
    $current = (string) old($key, $value);
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="label">{{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif {{ $attributes->except('class')->merge(['class' => 'input' . ($err ? ' input-error' : '')]) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $val => $text)
            <option value="{{ $val }}" @selected($current === (string) $val)>{{ $text }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if ($hint && !$err)<p class="hint">{{ $hint }}</p>@endif
    @if ($err)<p class="field-error">{{ $err }}</p>@endif
</div>
