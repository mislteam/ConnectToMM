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
        {{-- <div class="row">
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
        </div> --}}

        @php
            $activeTab = session('active_tab');

            if (!$activeTab) {
                $activeTab = $errors->getBag('rsp')->any() ? 'rsp' : 'warehouse';
            }
        @endphp

        <div class="row">
            <div class="col-12">
                <div class="card card-h-100 rounded-0 rounded-start">

                    <div class="card-header align-items-start">
                        <h3 class="mb-1 d-flex fs-xl align-items-center fw-semibold text-black">
                            API Credentials
                        </h3>
                    </div>

                    <div class="card-body px-4">

                        <ul class="nav nav-tabs mb-4" id="joytelApiTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'warehouse' ? 'active' : '' }}" id="warehouse-tab"
                                    data-bs-toggle="tab" data-bs-target="#warehouse-api" type="button" role="tab"
                                    aria-controls="warehouse-api"
                                    aria-selected="{{ $activeTab === 'warehouse' ? 'true' : 'false' }}">
                                    Warehouse API
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'rsp' ? 'active' : '' }}" id="rsp-tab"
                                    data-bs-toggle="tab" data-bs-target="#rsp-api" type="button" role="tab"
                                    aria-controls="rsp-api" aria-selected="{{ $activeTab === 'rsp' ? 'true' : 'false' }}">
                                    RSP API
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="joytelApiTabsContent">

                            {{-- Warehouse API Tab --}}
                            <div class="tab-pane fade {{ $activeTab === 'warehouse' ? 'show active' : '' }}"
                                id="warehouse-api" role="tabpanel" aria-labelledby="warehouse-tab">

                                <form method="POST" action="{{ route('joytel-api.update') }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label for="customer_code" class="form-label">
                                            {{ $settings['joytel_title']->value ?? 'Joytel' }} Customer Code
                                        </label>
                                        <input type="text" id="customer_code" name="customer_code" class="form-control"
                                            placeholder="Enter Joytel Customer Code"
                                            value="{{ old('customer_code', $api->customer_code ?? '') }}">

                                        @error('customer_code', 'warehouse')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_auth" class="form-label">
                                            {{ $settings['joytel_title']->value ?? 'Joytel' }} Customer Auth
                                        </label>
                                        <input type="text" id="customer_auth" name="customer_auth" class="form-control"
                                            placeholder="Enter Joytel Customer Auth"
                                            value="{{ old('customer_auth', $api->customer_auth ?? '') }}">

                                        @error('customer_auth', 'warehouse')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="api_url" class="form-label">API URL</label>
                                        <input type="text" id="api_url" name="api_url" class="form-control"
                                            placeholder="Enter API URL" value="{{ old('api_url', $api->api_url ?? '') }}">

                                        @error('api_url', 'warehouse')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            Save Warehouse API
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- RSP API Tab --}}
                            <div class="tab-pane fade {{ $activeTab === 'rsp' ? 'show active' : '' }}" id="rsp-api"
                                role="tabpanel" aria-labelledby="rsp-tab">

                                <form method="POST" action="{{ route('joytel-api.rsp.update') }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label for="rsp_appid" class="form-label">RSP App ID</label>
                                        <input type="text" id="rsp_appid" name="rsp_appid" class="form-control"
                                            placeholder="Enter RSP App ID"
                                            value="{{ old('rsp_appid', $api->rsp_appid ?? '') }}">

                                        @error('rsp_appid', 'rsp')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="rep_secret" class="form-label">RSP Secret</label>
                                        <input type="text" id="rep_secret" name="rsp_secret" class="form-control"
                                            placeholder="Enter RSP Secret"
                                            value="{{ old('rep_secret', $api->rsp_secret ?? '') }}">

                                        @error('rsp_secret', 'rsp')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="rsp_baseurl" class="form-label">RSP Base URL</label>
                                        <input type="text" id="rsp_baseurl" name="rsp_baseurl" class="form-control"
                                            placeholder="Enter RSP Base URL"
                                            value="{{ old('rsp_baseurl', $api->rsp_baseurl ?? '') }}">

                                        @error('rsp_baseurl', 'rsp')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            Save RSP API
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
