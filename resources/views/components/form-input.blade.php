@props(['label', 'name', 'type', 'value', 'placeholder', 'isrequired' => false])
<div class="form-group row mb-3">
    <label for="input" class="col-sm-2 col-form-label">
        <strong>{{ $label }}</strong>
        @if ($isrequired)
            <span class="text-danger">*</span>
        @endif
    </label>
    <div class="col-sm-10">
        <input id="input" type="{{ $type ?? 'text' }}" class="form-control @error($name) is-invalid @enderror"
            name="{{ $name }}" value="{{ html_entity_decode(old($name, $value ?? '')) }}"
            placeholder="{{ $placeholder ?? '' }}" @if ($requred ?? false) required @endif>
        @error($name)
            <small class="text-danger invalid-feedback d-block mt-2">{{ $message }}</small>
        @enderror
    </div>
</div>
