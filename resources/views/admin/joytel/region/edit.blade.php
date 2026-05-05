@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
@section('content')
    @include('components.joytel-alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }}</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }} - Edit
                        Region</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('region.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-xxl-8">
                <div class="card">
                    <div class="card-header d-block p-3">
                        <h4 class="card-title mb-1">Edit Region</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Usage Location</label>
                                    <input type="text" class="form-control" id="productName"
                                        value="{{ $region->location }}" disabled>
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
                    <form action="{{ route('region.update', $region->id) }}" method="POST">
                        @csrf
                        @method('put')
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="statusOne" class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="app-search">
                                    <select class="form-select form-control my-1 my-md-0" id="statusOne" name="status">
                                        <option>Choose Status</option>
                                        <option value="1" {{ $region->status == 1 ? 'selected' : '' }}>Enable</option>
                                        <option value="0" {{ $region->status == 0 ? 'selected' : '' }}>Disable</option>
                                    </select>
                                    <i data-lucide="toggle-left" class="app-search-icon text-muted"></i>
                                </div>
                            </div>
                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary disabled" id="updateBtn">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- end row -->
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectBox = document.getElementById('statusOne');
            const updateBtn = document.getElementById('updateBtn');

            if (selectBox && updateBtn) {
                selectBox.addEventListener('change', function() {
                    updateBtn.classList.remove('disabled');
                });
            }
        });
    </script>
@endsection
