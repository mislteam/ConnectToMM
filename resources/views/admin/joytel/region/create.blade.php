@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
@section('content')
    @include('components.joytel-alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Region</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Create</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('region.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <form action="{{ route('region.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-xxl-8">
                    <div data-table data-table-rows-per-page="8" class="card">
                        <div class="card-header border-light justify-content-between">
                            <h5 class="fw-semibold text-black">Region Create</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label for="skuId" class="form-label">Region Name <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" required=""
                                            placeholder="Enter Region Name (e.g. Myanmar)" name="location"
                                            data-region-names='@json($region_names)' id="region-input">
                                        <small class="text-danger" id="region-err"></small>
                                        @error('location')
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
                            <h4 class="card-title mb-1">Organize</h4>
                        </div> <!-- end card-header -->
                        <div class="card-body">
                            <div class="mb-0">
                                <label for="statusOne" class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="app-search">
                                    <select class="form-select form-control my-1 my-md-0" id="statusOne" name="status">
                                        <option value="">Choose Status</option>
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                    <i data-lucide="toggle-left" class="app-search-icon text-muted"></i>
                                </div>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mt-2 d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const regionInput = document.getElementById("region-input");
            const regionErr = document.getElementById("region-err");

            const region_names = JSON.parse(regionInput.getAttribute('data-region-names'));

            regionInput.addEventListener('input', function() {
                const value = regionInput.value.trim().toLowerCase();

                const exists = region_names.some(name => name.toLowerCase() === value);

                if (exists) {
                    regionInput.classList.add('is-invalid');
                    regionErr.textContent = "This Region Name already exists.";
                } else {
                    regionInput.classList.remove('is-invalid');
                    regionErr.textContent = "";
                }
            });
        });
    </script>

@endsection
