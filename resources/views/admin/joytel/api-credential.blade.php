@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }} API Credentials
                </h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }} API
                        Credentials</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card card-h-100 rounded-0 rounded-start">
                    <div class="card-header align-items-start">
                        <h3 class="mb-1 d-flex fs-xl align-items-center fw-semibold text-black">API Credentials</h3>
                    </div>
                    <form method="POST" action="{{ route('joytel-api.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="card-body px-4">
                            <div class="mb-3">
                                <label for="productName"
                                    class="form-label">{{ $settings['joytel_title']->value ?? 'joytel' }}
                                    Customer Code</label>
                                <input type="text" name="customer_code" class="form-control"
                                    placeholder="Enter Joytel Customer Code"
                                    value="{{ old('customer_code', $api->customer_code ?? '') }}">
                                @error('customer_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="productName"
                                    class="form-label">{{ $settings['joytel_title']->value ?? 'Joytel' }}
                                    Customer Auth</label>
                                <input type="text" name="customer_auth" class="form-control"
                                    placeholder="Enter Joytel Customer Auth"
                                    value="{{ old('customer_auth', $api->customer_auth ?? '') }}">
                                @error('customer_auth')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="productName" class="form-label">API URL</label>
                                <input type="text" name="api_url" class="form-control" placeholder="Enter API URL"
                                    value="{{ old('api_url', $api->api_url ?? '') }}">
                                @error('api_url')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div> <!-- end card-body -->
                    </form>
                </div> <!-- end card -->
            </div>
        </div>
    </div>

@endsection
