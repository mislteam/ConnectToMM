@extends('admin.layouts.index')
@section('title', 'Physical Sim')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">FiROAM</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Edit Product</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('roamEsimIndex') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xxl-8">
                <div class="card card-h-100 rounded-0 rounded-start">
                    <div class="card-header align-items-start px-4">
                        <h3 class="mb-1 d-flex fs-xl align-items-center fw-semibold text-black">
                            {{ $name['country_name'] ?? 'N/A' }}</h3>
                    </div>
                    <div class="card-body px-4">
                        <div class="accordion accordion-bordered" id="accordionExample">
                            @foreach ($roam['packages'] as $index => $plan)
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button shadow-none bg-light bg-opacity-50 collapsed"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $index }}" aria-expanded="true"
                                            aria-controls="collapseOne">
                                            <!-- {{ !empty(trim($plan['showName'] ?? '')) ? $plan['showName'] : 'No Daypass' }} -->
                                            {{ $plan['flows'] }} {{ $plan['unit'] }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse "
                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="row mb-4">
                                                <div class="col-md-4 col-xl-3">
                                                    <h6 class="mb-1 text-muted text-uppercase">Data:</h6>
                                                    <p class="fw-medium mb-0">{{ $plan['flows'] }} {{ $plan['unit'] }}</p>
                                                </div>
                                                <div class="col-md-4 col-xl-3">
                                                    <h6 class="mb-1 text-muted text-uppercase">Service Day:</h6>
                                                    <p class="fw-medium mb-0">{{ $plan['days'] }} Days</p>
                                                </div>
                                                <div class="col-md-4 col-xl-3">
                                                    <h6 class="mb-1 text-muted text-uppercase">Type:</h6>
                                                    <p class="fw-medium mb-0">Optional DayPass Plan</p>
                                                </div>
                                                <div class="col-md-4 col-xl-3">
                                                    <h6 class="mb-1 text-muted text-uppercase">Renewal:</h6>
                                                    <p class="fw-medium mb-0">
                                                        {{ $plan['flowType'] == '0' ? 'Available' : 'Non-Available' }}</p>
                                                </div>
                                                <div class="col-md-4 col-xl-3">
                                                    <h6 class="mb-1 text-muted text-uppercase">Price:</h6>
                                                    <p class="fw-medium mb-0">{{ $plan['price'] }} USD</p>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <h6 class="mb-1 text-muted text-uppercase">Support Network:</h6>
                                                <div class="table-responsive mt-2">
                                                    <table
                                                        class="table table-bordered table-nowrap text-center align-middle">
                                                        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                            <tr class="text-uppercase fs-xxs">
                                                                <th class="text-muted">#</th>
                                                                <th class="text-start text-muted">Network</th>
                                                                <th class="text-muted">Operator</th>
                                                                <th class="text-muted">Country</th>
                                                            </tr>
                                                        </thead>
                                                        @foreach ($plan['networkDtoList'] as $index => $network)
                                                            <tbody id="invoice-items">
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td class="text-start"><label
                                                                            class="form-label fw-medium mb-0">{{ $network['type'] }}</label>
                                                                    </td>
                                                                    <td><label
                                                                            class="form-label fw-medium mb-0">{{ $network['operator'] }}</label>
                                                                    </td>
                                                                    <td><label
                                                                            class="form-label fw-medium mb-0">{{ $network['nameen'] }}</label>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <h5 class="fs-base mb-2">Description:</h5>
                                                <p class="text-muted">
                                                    {{ $plan['premark'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col-->


            <div class="col-xxl-4">
                <div class="card">
                    <div class="card-header d-block p-3">
                        <h4 class="card-title mb-1">Product Image</h4>
                    </div> <!-- end card-header -->

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <form action="{{ route('roam.update', $roam->id) }}" method="POST" id="roamForm"
                                    enctype="multipart/form-data" class="dropzone" data-plugin="dropzone"
                                    data-previews-container="#file-previews">
                                    @csrf
                                    @method('PUT')

                                    {{-- Hidden file input --}}
                                    <input type="file" name="image" id="imageInput" hidden accept="image/*">

                                    {{-- Cloud Icon & Browse --}}
                                    <div class="dz-message needsclick text-center">
                                        @if (!empty($roam->image))
                                            <div class="text-center mb-3">
                                                <img id="previewImage"
                                                    src="{{ file_exists(public_path('storage/upload/roam/' . $roam->image)) ? asset('storage/upload/roam/' . $roam->image) : asset($roam->image ?? 'assets/images/package.jpg') }}"
                                                    alt="Current Image" class="img-fluid rounded"
                                                    style="max-height: 150px;">
                                            </div>
                                        @else
                                            <img id="previewImage" src="#" alt="Preview"
                                                class="img-fluid rounded d-none mb-3" style="max-height: 150px;">
                                        @endif

                                        <div class="avatar-lg mx-auto mb-3" style="cursor:pointer;" id="cloudIcon">
                                            <span class="avatar-title bg-info-subtle text-info rounded-circle">
                                                <i class="fs-24 ti ti-cloud-upload"></i>
                                            </span>
                                        </div>

                                        <h4 class="mb-2">Drop files here or click to upload.</h4>
                                        <p class="text-muted fst-italic mb-3">You can drag images here, or browse files via
                                            the button below.</p>

                                        <button type="button" class="btn btn-sm shadow btn-default" id="browseBtn">
                                            Browse Images
                                        </button>
                                    </div>
                            </div>
                            <!-- {{-- Dropzone Previews --}}
                                                <div class="dropzone-previews mt-3" id="file-previews"></div> -->
                            <div class="col-12">
                                {{-- Status Section --}}
                                <div class="card mt-3">
                                    <div class="card-header d-block p-3">
                                        <h4 class="card-title mb-1">Organize</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="statusOne" class="form-label">Status <span
                                                    class="text-danger">*</span></label>
                                            <div class="app-search">
                                                <select name="status" class="form-select form-control my-1 my-md-0"
                                                    id="statusOne" required>
                                                    <option value="">Choose Status</option>
                                                    <option value="1"
                                                        {{ $roam->roamSku->status == 1 ? 'selected' : '' }}>Enable</option>
                                                    <option value="0"
                                                        {{ $roam->roamSku->status == 0 ? 'selected' : '' }}>Disable
                                                    </option>
                                                </select>
                                                <i data-lucide="toggle-left" class="app-search-icon text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary mt-3">Update</button>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div> <!-- end row-->


        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const browseBtn = document.getElementById('browseBtn');
                const cloudIcon = document.getElementById('cloudIcon');
                const imageInput = document.getElementById('imageInput');
                const previewImage = document.getElementById('previewImage');

                // Open file picker on click
                browseBtn.addEventListener('click', () => imageInput.click());
                cloudIcon.addEventListener('click', () => imageInput.click());

                // Show preview when file is selected
                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewImage.classList.remove('d-none');
                        }
                        reader.readAsDataURL(file);
                    }
                });
            });
        </script>
    @endsection
