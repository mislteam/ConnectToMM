@props(['name', 'index', 'type' => 'text', 'placeholder' => '', 'inputClass' => '', 'value' => null])

@php
    $field = $name . '.' . $index;
@endphp

<div class="mb-3">
    <input type="{{ $type }}" name="{{ $name }}[{{ $index }}]" value="{{ old($field, $value) }}"
        placeholder="{{ $placeholder }}" class="form-control {{ $inputClass }} @error($field) is-invalid @enderror">

    @error($field)
        <small class="text-danger invalid-feedback">{{ $message }}</small>
    @enderror

    <small class="text-success valid-feedback">Looks good!</small>

    {{ $slot }}
</div>
