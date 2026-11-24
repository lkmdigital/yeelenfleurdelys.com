@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'id' => null])
@php
    $id = $id ?? 'f_' . str_replace(['[', ']', '.'], '_', $name);
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $err = $errors->first($key);
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="label">{{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif</label>
    @endif
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        @if ($type !== 'password' && $type !== 'file') value="{{ old($key, $value) }}" @endif
        @if ($required) required @endif
        @if ($err) aria-invalid="true" aria-describedby="{{ $id }}_err" @endif
        {{ $attributes->except('class')->merge(['class' => 'input' . ($err ? ' input-error' : '')]) }}>
    @if ($hint && !$err)<p class="hint">{{ $hint }}</p>@endif
    @if ($err)<p id="{{ $id }}_err" class="field-error">{{ $err }}</p>@endif
</div>
