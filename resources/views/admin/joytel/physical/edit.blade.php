@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
<style>
    th.t-head {
        min-width: 200px;
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
                <h4 class="fs-sm fw-bold m-0 text-black">Joytel</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Edit Product</li>
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
                                        <label for="productName" class="form-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="product_name" class="form-control" id="productName"
                                            placeholder="Enter Product Name" required=""
                                            value="{{ old('product_name', $recharge->product_name) }}">
                                        @error('product_name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="categoryName" class="form-label">Category Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="cat_name" class="form-control" id="categoryName"
                                            placeholder="Enter Category Name" required="" readonly
                                            value="{{ $recharge->category_name }}">
                                        @error('cat_name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="stockNumber" class="form-label">Supplier <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier" class="form-control" id="stockNumber"
                                            placeholder="Enter Suppliers" readonly
                                            value="{{ old('supplier', $recharge->supplier) }}">
                                        @error('supplier')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="stockNumber" class="form-label">Product Type <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="product_type" class="form-control" id="stockNumber"
                                            placeholder="Enter Product Type(eg. Recharge)" readonly
                                            value="{{ old('product_type', $recharge->product_type) }}">
                                        @error('product_type')
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
                                                    <th class="t-head">Network Type</th>
                                                    <th class="t-head">Price CNY</th>
                                                    <th class="t-head">Remark</th>
                                                    <th class="t-head">Expiration date</th>
                                                    <th class="t-head">Description</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="invoice-items">
                                                @foreach ($recharge->plan as $key => $plan)
                                                    <tr>
                                                        <td class="row-id">{{ $loop->iteration }}</td>
                                                        <!-- sku -->
                                                        <td>
                                                            <input type="text"
                                                                name="rows[{{ $loop->iteration }}][product_code]"
                                                                class="form-control data-input" data-field="product_code"
                                                                placeholder="Enter SKU" readonly
                                                                value="{{ $plan['product_code'] ?? '' }}">
                                                            @error('product_code')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>

                                                        <td class="d-none">
                                                            <!-- code status -->
                                                            <input type="hidden"
                                                                name="rows[{{ $loop->iteration }}][code_status]"
                                                                class="data-input" data-field="code_status" readonly
                                                                value="{{ $plan['code_status'] ?? '' }}">
                                                        </td>

                                                        <!-- data -->
                                                        <td>
                                                            <input type="text"
                                                                name="rows[{{ $loop->iteration }}][data]"
                                                                class="form-control data-input" data-field="data"
                                                                placeholder="Enter Data" readonly
                                                                value="{{ $plan['data'] ?? '' }}">
                                                            @error('data')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <!-- traffic type -->
                                                        {{-- dynamic traffic type --}}
                                                        <td>
                                                            <select class="form-select data-input"
                                                                data-field="traffic_type"
                                                                name="rows[{{ $loop->iteration }}][traffic_type]">
                                                                <option value="">Select Traffic Type</option>
                                                                <option value="Daily Type"
                                                                    {{ $plan['traffic_type'] == 'Daily Type' ? 'selected' : '' }}>
                                                                    Daily Type</option>
                                                                <option value="Total Type"
                                                                    {{ $plan['traffic_type'] == 'Total Type' ? 'selected' : '' }}>
                                                                    Total Type</option>
                                                                <option value="Unlimited Type"
                                                                    {{ $plan['traffic_type'] == 'Unlimited Type' ? 'selected' : '' }}>
                                                                    Unlimited Type</option>
                                                            </select>
                                                            @error('traffic_type')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <!-- service day -->
                                                        <td>
                                                            <input type="text"
                                                                name="rows[{{ $loop->iteration }}][service_day]"
                                                                class="form-control data-input" data-field="service_day"
                                                                placeholder="Enter Service Day" readonly
                                                                value="{{ $plan['service_day'] }}">
                                                            @error('service_day')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="network_type" placeholder="Enter Network Type"
                                                                name="rows[{{ $loop->iteration }}][network_type]" readonly
                                                                value="{{ $plan['network_type'] ?? '' }}">
                                                        </td>

                                                        <td>
                                                            <input type="number" class="form-control data-input"
                                                                data-field="price_cny"
                                                                name="rows[{{ $loop->iteration }}][price_cny]"
                                                                placeholder="Enter Amount" step="0.01" min="0"
                                                                readonly value="{{ (float) $plan['price_cny'] }}">
                                                            @error('price_cny')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="remark"
                                                                name="rows[{{ $loop->iteration }}][remark]"
                                                                placeholder="Enter remark"
                                                                value="{{ $plan['remark'] ?? '' }}">
                                                            @error('remark')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="date" class="form-control data-input"
                                                                data-field="expiration_date"
                                                                name="rows[{{ $loop->iteration }}][expiration_date]"
                                                                placeholder="Enter Expiration Date" readonly
                                                                value="{{ $plan['expiration_date'] ?? '' }}">
                                                            @error('expiration_date')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control data-input"
                                                                data-field="description"
                                                                name="rows[{{ $loop->iteration }}][description]"
                                                                placeholder="Enter Description" readonly
                                                                value="{{ $plan['description'] ?? '' }}">
                                                            @error('description')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger removeBtn"
                                                                {{ $key === 0 ? 'disabled' : '' }}>×</button>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                        <div class="mt-2 mb-4 d-flex gap-2 justify-content-start">
                                            <button type="button" class="btn btn-primary text-end" id="addBtn"><i
                                                    class="ti ti-plus"></i> Add Item</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div>
                                        <label class="form-label">Activation Policy</label>
                                        <textarea rows="3" name="activation_policy" class="form-control mb-2"
                                            placeholder="Enter the Activation Policy">{{ old('activation_policy', $recharge->activation_policy) }}</textarea>
                                        @error('activation_policy')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div>
                                        <label class="form-label">Delivery Time</label>
                                        <textarea rows="3" name="del_time" class="form-control mb-2" placeholder="Enter the Delivery Time">{{ old('del_time', $recharge->delivery_time) }}</textarea>
                                        @error('del_time')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
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
                                    <!-- end file preview template -->
                                </div>

                            </div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Organize</h4>
                        </div> <!-- end card-header -->
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="category" class="form-label">Region Name <span
                                        class="text-danger">*</span></label>

                                <div class="app-search">
                                    <select class="select2_design form-select form-control my-1 my-md-0"
                                        multiple="multiple" name="locations[]">
                                        @foreach ($usage_locations as $location)
                                            <option value="{{ $location }}"
                                                {{ in_array($location, $recharge->usage_location) ? 'selected' : '' }}>
                                                {{ $location }}</option>
                                        @endforeach
                                    </select>
                                    {{-- <i data-lucide="grid" class="app-search-icon text-muted"></i> --}}
                                    @error('locations[]')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="statusOne" class="form-label">Status <span
                                        class="text-danger">*</span></label>
                                <div class="app-search">
                                    <select class="form-select form-control my-1 my-md-0" id="statusOne" name="status">
                                        <option>Choose Status</option>
                                        <option value="1" {{ $recharge->status === 1 ? 'selected' : '' }}>Enable
                                        </option>
                                        <option value="0" {{ $recharge->status === 0 ? 'selected' : '' }}>Disable
                                        </option>
                                    </select>
                                    <i data-lucide="toggle-left" class="app-search-icon text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Drop Zone
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

                            input.disabled = true;
                        }
                    }
                });

                // form
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
                            if (data.success && data.redirect_url) {
                                window.location.href = data.redirect_url;
                            }
                        })
                        .catch(err => console.error(err));
                });
            }

        };


        // clone
        const tbody = document.getElementById('invoice-items');
        const addBtn = document.getElementById('addBtn');

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

        addBtn.addEventListener('click', function() {
            const firstRow = tbody.querySelector('tr');
            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('input, select').forEach(input => {
                input.value = "";
                input.removeAttribute('value');
                input.removeAttribute('readonly');
            });
            tbody.appendChild(clone);
            updateIds();
        });

        tbody.addEventListener('click', (e) => {
            if (e.target.classList.contains('removeBtn')) {
                e.target.closest('tr').remove();
                updateIds();
            }
        });
    });
</script>
