@extends('admin.layouts.index')
@section('title', 'View Message')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Message</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">View Message</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <a href="{{ route('message.index') }}" class="btn btn-primary ms-1">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-block p-3">
                        <h4 class="card-title mb-1">View Message</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="col-form-label">Username:</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p>{{ $message->name }}</p>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="col-form-label">Email:</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></p>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="col-form-label">Phone:</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p><a href="tel:{{ $message->phone }}">{{ $message->phone }}</a></p>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="col-form-label">Message:</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p>{{ $message->message }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
