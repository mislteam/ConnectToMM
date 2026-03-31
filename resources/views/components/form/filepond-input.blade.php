@props(['label', 'name', 'isrequired' => false])

<div class="row">
    <div class="col-lg-2">
        <div class="mb-3">
            <label class="col-form-label">
                {{ $label }}
                @if ($isrequired)
                    <span class="text-danger">*</span>
                @endif
            </label>
        </div>
    </div>
    <div class="col-lg-10">
        <div class="mb-3">
            <div class="filepond-uploader">
                <input type="file" class="filepond filepond-input-multiple" accept="image/png, image/jpeg"
                    name="{{ $name }}" data-allow-reorder="true" data-max-file-size="2MB" data-max-files="1">
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
