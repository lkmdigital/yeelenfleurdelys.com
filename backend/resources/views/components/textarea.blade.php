@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false, 'rows' => 4, 'id' => null])
@php
    $id = $id ?? 'f_' . str_replace(['[', ']', '.'], '_', $name);
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $err = $errors->first($key);
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="label">{{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) required @endif {{ $attributes->except('class')->merge(['class' => 'input' . ($err ? ' input-error' : '')]) }}>{{ old($key, $value) }}</textarea>
    @if ($hint && !$err)<p class="hint">{{ $hint }}</p>@endif
    @if ($err)<p class="field-error">{{ $err }}</p>@endif
</div>
