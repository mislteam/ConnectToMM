@props(['value', 'type', 'image', 'orderTypes' => null])

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

<div class="row mb-2">
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="col-form-label">Our Services</label>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="mb-3">
            <div class="d-flex justify-content-between col-lg-6">
                <div class="d-flex justify-contents-center align-items-center">
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input name="{{ $type . '_esim' }}" type="checkbox" value="1"
                            class="form-check-input mt-1 code-status-toggle"
                            {{ $simTypes['esim'] === 1 ? 'checked' : '' }}>
                    </div>
                    <label>ESIM</label>
                </div>

                <div class="d-flex justify-contents-center align-items-center w-25">
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input name="{{ $type . '_physical' }}" type="checkbox"
                            class="form-check-input mt-1 code-status-toggle" value="1"
                            {{ $simTypes['physical'] ? 'checked' : '' }}>
                    </div>
                    <label>Physical SIM Recharge</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="col-form-label">eSIM Order Type</label>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="mb-3">
            <div class="d-flex justify-content-between col-lg-6">
                <div class="d-flex justify-contents-center align-items-center">
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input name="{{ $type . '_esim_new' }}" type="checkbox"
                            class="form-check-input mt-1 code-status-toggle" value="esim_new"
                            {{ in_array('esim_new', $orderTypes ?? []) ? 'checked' : '' }}>
                    </div>
                    <label>New eSIM</label>
                </div>

                <div class="d-flex justify-contents-center align-items-center w-25">
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input name="{{ $type . '_esim_recharge' }}" type="checkbox"
                            class="form-check-input mt-1 code-status-toggle" value="esim_recharge"
                            {{ in_array('esim_recharge', $orderTypes ?? []) ? 'checked' : '' }}>
                    </div>
                    <label>Recharge</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-2">
    <div class="col-lg-3">
        <div class="mb-3">
            <label class="col-form-label">Physical Order Type</label>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="mb-3">
            <div class="d-flex justify-content-between col-lg-6">
                <div class="d-flex justify-contents-center align-items-center">
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input name="{{ $type . '_physical_new' }}" type="checkbox"
                            class="form-check-input mt-1 code-status-toggle" value="physical_new"
                            {{ in_array('physical_new', $orderTypes ?? []) ? 'checked' : '' }}>
                    </div>
                    <label>New eSIM</label>
                </div>

                <div class="d-flex justify-contents-center align-items-center w-25">
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input name="{{ $type . '_physical_recharge' }}" type="checkbox"
                            class="form-check-input mt-1 code-status-toggle" value="physical_recharge"
                            {{ in_array('physical_recharge', $orderTypes ?? []) ? 'checked' : '' }}>
                    </div>
                    <label>Recharge</label>
                </div>
            </div>
        </div>
    </div>
</div>
