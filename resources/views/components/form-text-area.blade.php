@props(['label', 'name', 'value', 'placeholder' => '', 'isrequired' => false])
<div class="form-group row mb-3">
    <label class="col-sm-2 col-form-label">
        {{ $label }}
        @if ($isrequired)
            <span class="text-danger">*</span>
        @endif
    </label>
    <div class="col-sm-10">
        <textarea rows="3" name="{{ $name }}" class="form-control" placeholder="{{ $placeholder }}">{{ old($name, $value ?? '') }}</textarea>
    </div>
    @error('{{ $name }}')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
