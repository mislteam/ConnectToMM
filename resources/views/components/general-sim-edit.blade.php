@props(['value', 'type', 'image'])

<div class="row mb-3">
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="col-form-label">Title</label>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="mb-3">
            <input type="text" name="{{ $type . '_title' }}" class="form-control" placeholder="Enter Title" required
                value="{{ $value }}">
            <div class="my-1">
                @error("{{ $type . '_title' }}")
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="col-form-label">Logo</label>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="mb-3">
            <div class="filepond-uploader">
                <input type="file" class="filepond filepond-input-multiple" multiple name="{{ $type . '_image' }}"
                    data-allow-reorder="true" data-max-file-size="3MB" data-max-files="5"
                    accept="image/png,image/jpg,image/jpeg">
            </div>
            <div class="mt-3">
                @if ($image)
                    <a target="_blank" href="{{ asset('general/logo/' . $image->value) }}"
                        alt="sim_img">{{ asset('general/logo/' . $image->value) }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
