@extends('admin.layouts.index')
@section('title', 'Joytel Physical')
<style>
    th.t-head {
        min-width: 200px;
    }

    .joytel-physical-edit-page-title,
    .joytel-physical-edit-breadcrumb-current {
        color: #111827;
    }

    .joytel-physical-edit-breadcrumb-link {
        color: #4b5563;
    }

    .joytel-physical-edit-breadcrumb-link:hover {
        color: #1f2937;
    }

    html[data-bs-theme="dark"] .joytel-physical-edit-page-title,
    html[data-bs-theme="dark"] .joytel-physical-edit-breadcrumb-current {
        color: #e5edf9;
    }

    html[data-bs-theme="dark"] .joytel-physical-edit-breadcrumb-link {
        color: #9fb1cc;
    }

    html[data-bs-theme="dark"] .joytel-physical-edit-breadcrumb-link:hover {
        color: #dbe7ff;
    }

    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        color: #fff !important;
    }

    #swal2-html-container {
        font-size: 14px !important;
    }
</style>
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 joytel-physical-edit-page-title">
                    {{ $settings['joytel_title']->value ?? 'Joytel' }}</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);"
                            class="joytel-physical-edit-breadcrumb-link">Home</a></li>

                    <li class="breadcrumb-item active joytel-physical-edit-breadcrumb-current">Edit Product</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('physical.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <form action="{{ route('physical.update', $recharge->id) }}" method="POST" enctype="multipart/form-data"
            id="myForm">
            @csrf
            @method('patch')
            <div class="row">
                <div class="col-xxl-8">
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Product Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="productName" class="form-label">Product Name </label>
                                        <input type="text" name="product_name" class="form-control" id="productName"
                                            placeholder="Enter Product Name"
                                            value="{{ old('product_name', $recharge->product_name) }}" disabled>
                                        @error('product_name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="table-responsive mt-2">
                                        <table class="table table-bordered table-nowrap text-center align-middle">
                                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                <tr class="text-uppercase fs-xxs">
                                                    <th>#</th>
                                                    <th class="t-head text-start">Product Code</th>
                                                    <th class="t-head">Data</th>
                                                    <th class="t-head">Traffic Type</th>
                                                    <th class="t-head">Service Day</th>
                                                    <th class="t-head">Price CNY</th>
                                                    <th class="t-head">Type</th>
                                                    <th class="t-head">Product Description</th>
                                                    <th class="t-head">Memo</th>
                                                    <th class="t-head">Activation Type</th>
                                                    <th class="t-head">Provider</th>
                                                    <th class="t-head">Network Type</th>
                                                    <th class="t-head">Hotspot</th>
                                                    <th class="t-head">Recharge</th>

                                                </tr>
                                            </thead>
                                            <tbody id="invoice-items">

                                                @foreach ($plans as $plan)
                                                    <tr>
                                                        <td class="row-id">{{ $loop->iteration }}</td>
                                                        <!-- sku -->
                                                        <td>
                                                            <input type="text" name="code"
                                                                class="form-control data-input" data-field="code"
                                                                placeholder="Enter SKU" value="{{ $plan['code'] ?? '' }}"
                                                                readonly>
                                                            @error('product_code')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>

                                                        <td class="d-none">
                                                            <!-- code status -->
                                                            <input type="hidden" name="row_status" class="data-input"
                                                                data-field="status" value="{{ $plan['status'] ?? '' }}">
                                                        </td>

                                                        <!-- data -->
                                                        <td>
                                                            <input type="text" name="data"
                                                                class="form-control data-input" data-field="data"
                                                                data-field="data" placeholder="Enter Data"
                                                                value="{{ $plan['data'] ?? '' }}" readonly>
                                                            @error('data')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <!-- traffic type -->
                                                        {{-- dynamic traffic type --}}
                                                        <td>
                                                            <select class="form-select data-input" data-field="traffic_type"
                                                                name="traffic_type" disabled>
                                                                <option value="">Select Traffic Type</option>
                                                                <option value="Daily Type"
                                                                    {{ $plan['traffic_type'] == 'daily' ? 'selected' : '' }}>
                                                                    Daily Type</option>
                                                                <option value="Total Type"
                                                                    {{ $plan['traffic_type'] == 'total' ? 'selected' : '' }}>
                                                                    Total Type</option>
                                                                <option value="Unlimited Type"
                                                                    {{ $plan['traffic_type'] == 'unlimited' ? 'selected' : '' }}>
                                                                    Unlimited Type</option>
                                                            </select>
                                                            @error('traffic_type')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <!-- service day -->
                                                        <td>
                                                            <input type="text" name="service_day"
                                                                class="form-control data-input" data-field="service_day"
                                                                placeholder="Enter Service Day"
                                                                value="{{ $plan['service_day'] }}" readonly>
                                                            @error('service_day')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>

                                                        <td>
                                                            <input type="number" class="form-control data-input"
                                                                data-field="price" name="price" placeholder="Enter Amount"
                                                                step="0.01" min="0"
                                                                value="{{ (float) $plan['price'] }}" readonly>
                                                            @error('price')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="type" name="type"
                                                                placeholder="Enter remark"
                                                                value="{{ $plan['type'] ?? '' }}" readonly>
                                                            @error('type')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="product_description"
                                                                name="product_description" placeholder="Enter Description"
                                                                value="{{ $plan['product_description'] ?? '' }}" readonly>
                                                            @error('product_description')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="memo" name="memo" placeholder="Enter Memo"
                                                                value="{{ $plan['memo'] ?? '' }}" readonly>
                                                            @error('memo')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="activation_type"
                                                                placeholder="Enter Activation Type" name="activation_type"
                                                                value="{{ $plan['activation_type'] ?? '' }}" readonly>
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="provider" placeholder="Enter provider"
                                                                name="provider" value="{{ $plan['provider'] ?? '' }}"
                                                                readonly>
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="network" placeholder="Enter Network Type"
                                                                name="network" value="{{ $plan['network'] ?? '' }}"
                                                                readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="hotspot" placeholder="Enter Network Type"
                                                                name="hotspot" value="{{ $plan['hotspot'] ?? '' }}"
                                                                readonly>
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="recharge" placeholder="Enter Network Type"
                                                                name="recharge" value="{{ $plan['recharge'] ?? '' }}"
                                                                readonly>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                        {{-- <div class="mt-2 mb-4 d-flex gap-2 justify-content-start">
                                            <button type="button" class="btn btn-primary text-end" id="addBtn"><i
                                                    class="ti ti-plus"></i> Add Item</button>
                                        </div> --}}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4">
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Product Image</h4>
                        </div> <!-- end card-header -->

                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    @if ($recharge->photo !== null && is_array($recharge->photo))
                                        @foreach ($recharge->photo as $photo)
                                            <input type="hidden" name="old_photos[]" value="{{ $photo }}"
                                                data-filename="{{ basename($photo) }}">
                                        @endforeach
                                    @endif

                                    <div class="dropzone" id="myAwesomeDropzone" data-plugin="dropzone"
                                        data-previews-container="#file-previews"
                                        data-upload-preview-template="#uploadPreviewTemplate">
                                        <div class="fallback">
                                            <input name="file" type="file" name="files[]" multiple>
                                        </div>

                                        <div class="dz-message needsclick">
                                            <div class="avatar-lg mx-auto mb-3">
                                                <span class="avatar-title bg-info-subtle text-info rounded-circle">
                                                    <i class="fs-24 ti ti-cloud-upload"></i>
                                                </span>
                                            </div>
                                            <h4 class="mb-2">Drop files here or click to upload.</h4>
                                            <p class="text-muted fst-italic mb-3">You can drag images here, or
                                                browse files via the button below.</p>
                                            <button type="button" class="btn btn-sm shadow btn-default">Browse
                                                Images
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Preview -->
                                    <div class="dropzone-previews mt-3" id="file-previews">
                                    </div>

                                    <!-- file preview template -->
                                    <div class="d-none" id="uploadPreviewTemplate">
                                        <div class="card mt-1 mb-0 border-dashed border">
                                            <div class="p-2">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <img data-dz-thumbnail src="#"
                                                            class="avatar-sm rounded bg-light" alt="">
                                                    </div>
                                                    <div class="col ps-0">
                                                        <a href="javascript:void(0);" class="fw-semibold"
                                                            data-dz-name></a>
                                                        <p class="mb-0 text-muted" data-dz-size></p>
                                                    </div>
                                                    <div class="col-auto">
                                                        <!-- Button -->
                                                        <a href="" class="btn btn-link btn-lg text-danger"
                                                            data-dz-remove>
                                                            <i class="ti ti-x"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card mt-3">
                                            <div class="card-header d-block p-3">
                                                <h4 class="card-title mb-1">Organize</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="category" class="form-label">Region Name
                                                    </label>
                                                    <div class="app-search">
                                                        <select
                                                            class="select2_design form-select form-control my-1 my-md-0"
                                                            multiple="multiple" name="locations[]"
                                                            data-placeholder="Choose Region" disabled>
                                                            @php
                                                                $selectedCoverage = collect($recharge->coverage ?? [])
                                                                    ->flatMap(function ($item) use ($coverages) {
                                                                        $values = [$item];

                                                                        if (!$coverages->contains($item)) {
                                                                            $values[] = trim(
                                                                                preg_replace(
                                                                                    '/\s*\([^)]*\)$/',
                                                                                    '',
                                                                                    $item,
                                                                                ),
                                                                            );
                                                                        }

                                                                        return $values;
                                                                    })
                                                                    ->unique()
                                                                    ->toArray();
                                                            @endphp

                                                            @foreach ($coverages as $location)
                                                                <option value="{{ $location }}"
                                                                    {{ in_array($location, $selectedCoverage) ? 'selected' : '' }}>
                                                                    {{ $location }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('locations[]')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="statusOne" class="form-label">Status <span
                                                                class="text-danger">*</span></label>
                                                        <div class="app-search">
                                                            <select class="form-select form-control" id="statusOne"
                                                                name="status" required>
                                                                <option value="1"
                                                                    {{ $recharge->status === 1 ? 'selected' : '' }}>Enable
                                                                </option>
                                                                <option value="0"
                                                                    {{ $recharge->status === 0 ? 'selected' : '' }}>Disable
                                                                </option>
                                                            </select>
                                                            <i data-lucide="toggle-left"
                                                                class="app-search-icon text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Dropzone
        Dropzone.options.myAwesomeDropzone = {
            autoProcessQueue: false,
            acceptedFiles: 'image/*',
            init: function() {
                const myDropzone = this;
                const form = document.getElementById('myForm');
                // Load old photos
                @if (!empty($recharge->photo) && is_array($recharge->photo))
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'old_photos[]';
                    input.value = "{{ $photo }}";
                    input.dataset.filename = "{{ basename($photo) }}";
                    form.appendChild(input);
                    @foreach ($recharge->photo as $photo)
                        @if (!empty($photo))
                            (function() {
                                const mockFile = {
                                    name: "{{ basename($photo) }}",
                                    size: 12345,
                                    isExisting: true // mark old files
                                };
                                myDropzone.emit("addedfile", mockFile);
                                myDropzone.emit("thumbnail", mockFile,
                                    "{{ asset('sim/' . $photo) }}");
                                myDropzone.emit("complete", mockFile);
                                myDropzone.files.push(mockFile);

                                // Add hidden input for old photo
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'old_photos[]';
                                input.value = "{{ $photo }}";
                                input.dataset.filename =
                                    "{{ basename($photo) }}"; // store filename
                                form.appendChild(input);
                            })();
                        @endif
                    @endforeach
                @endif

                myDropzone.on("removedfile", function(file) {
                    if (file.isExisting) {
                        const input = document.querySelector(
                            `input[name="old_photos[]"][data-filename="${file.name}"]`);
                        if (input) {
                            // Add a hidden field to tell backend this file was removed
                            const removedInput = document.createElement('input');
                            removedInput.type = 'hidden';
                            removedInput.name = 'removed_photos[]';
                            removedInput.value = input.value;
                            myDropzone.element.appendChild(removedInput);

                            // Optionally hide or disable original input so validation passes
                            input.disabled = true;
                        }
                    }
                });

                // Your existing form submit handler
                form.addEventListener("submit", function(e) {
                    e.preventDefault();
                    const rows = [];
                    document.querySelectorAll('#invoice-items tr').forEach(tr => {
                        const rowData = {};
                        tr.querySelectorAll('.data-input').forEach(input => {
                            const field = input.dataset.field;
                            rowData[field] = input.value;
                        });
                        rows.push(rowData);
                    });
                    console.log(rows);

                    const formData = new FormData(form);
                    formData.append('rows_json', JSON.stringify(rows));

                    myDropzone.files.forEach(file => {
                        if (!file.isExisting) {
                            formData.append("files[]", file);
                        }
                    });

                    fetch(form.action, {
                            method: "POST",
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(async res => {
                            if (!res.ok) {
                                const text = await res.text();
                                let msg = text;
                                try {
                                    const json = JSON.parse(text);
                                    msg = json.message || text;
                                } catch (e) {}
                                Swal.fire({
                                    title: 'Error',
                                    text: msg,
                                    confirmButtonColor: '#0049ad'
                                });
                                throw new Error(msg);
                            }
                            return res.json();
                        })
                        .then(data => {
                            // console.log(data);
                            if (data.success && data.redirect_url) {
                                // console.log(data);
                                window.location.href = data.redirect_url;
                            }
                        })
                        .catch(err => console.error(err));
                });
            }

        };
        // Table clone functionality
        const tbody = document.getElementById('invoice-items');
        const addBtn = document.getElementById('addBtn');
        document.querySelectorAll('.removeBtn').forEach(button => button.disabled = true);

        function updateIds() {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((row, i) => {
                const rowNum = i + 1;
                row.querySelector('.row-id').textContent = rowNum;
                row.querySelectorAll('.data-input').forEach(input => {
                    const field = input.dataset.field;
                    input.name =
                        `rows[${rowNum}][${field}]`; // optional if you want individual field names
                });
                const removeBtn = row.querySelector('.removeBtn');
                removeBtn.disabled = (i === 0);
            });
        }

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const firstRow = tbody.querySelector('tr');
                const clone = firstRow.cloneNode(true);
                clone.querySelectorAll('input, select').forEach(input => {
                    input.value = "";
                    input.removeAttribute('value');
                });
                tbody.appendChild(clone);
                updateIds();
            });
        }

        tbody.addEventListener('click', (e) => {
            if (e.target.classList.contains('removeBtn')) {
                e.target.closest('tr').remove();
                updateIds();
            }
        });
    });
</script>
