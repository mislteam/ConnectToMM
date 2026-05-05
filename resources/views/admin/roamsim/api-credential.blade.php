@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">{{ $settings['roam_title']->value ?? 'Roam' }} API Credentials</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">{{ $settings['roam_title']->value ?? 'Roam' }} API
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
                    <form method="POST" action="{{ route('roam-api.store') }}">
                        @csrf
                        <div class="card-body px-4">
                            <div class="mb-3">
                                <label for="productName" class="form-label">{{ $settings['roam_title']->value ?? 'Roam' }}
                                    Client ID</label>
                                <input type="text" name="client_id" class="form-control"
                                    placeholder="Enter ROAM Client ID(phone number)"
                                    value="{{ old('client_id', $api->client_id ?? '') }}">
                                @error('client_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="productName" class="form-label">{{ $settings['roam_title']->value ?? 'Roam' }}
                                    Client Secret</label>
                                <input type="text" name="client_secret" class="form-control"
                                    placeholder="Enter Roam Client Secret (password)"
                                    value="{{ old('secret_key', $api->secret_key ?? '') }}">
                                @error('client_secret')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="productName" class="form-label">{{ $settings['roam_title']->value ?? 'Roam' }}
                                    Client Key</label>
                                <input type="text" name="client_key" class="form-control"
                                    placeholder="Enter Roam Client Key"
                                    value="{{ old('client_key', $api->client_key ?? '') }}">
                                @error('client_key')
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
