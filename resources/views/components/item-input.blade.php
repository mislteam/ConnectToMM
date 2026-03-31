@props(['label', 'name', 'type', 'value', 'placeholder'])
<div class="form-group row mb-3">
    <label class="col-sm-2 col-form-label"><strong>{{ $label }}</strong><span class="text-danger">*</span></label>
    <div class="col-sm-10">
        <input type="{{ $type ?? 'text' }}" class="form-control" name="{{ $name }}"
            value="{{ old($name, $value ?? '') }}" placeholder="{{ $placeholder ?? '' }}"
            @if ($requred ?? false) required @endif>
    </div>
    @error('{{ $name }}')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
