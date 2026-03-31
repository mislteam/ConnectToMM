@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content')
<div class="container-fluid">               
                <div class="page-title-head d-flex align-items-center">
                    <div class="flex-grow-1 py-3">
                        <h4 class="fs-sm fw-bold m-0 text-black">Profile</h4>
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                            <li class="breadcrumb-item active text-black">All Admin</li>
                        </ol>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex gap-1">
                            <a href="{{ url()->previous() }}" class="btn btn-primary ms-1">Back</a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div data-table data-table-rows-per-page="8" class="card">
                            <div class="card-header d-block">
                                <h4 class="card-title mb-1 text-black">Account Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-start align-items-center gap-3">
                                    <div class="avatar avatar-xxl">
                                        <img src="{{ $users->profile_image ? asset('storage/profile_images/' . $users->profile_image) : asset('assets/images/user-3.jpg') }}" alt="avatar-2" class="img-fluid rounded-circle">
                                    </div>
                                    <div>
                                        <p class="text-dark mb-1"><strong>Name :</strong>{{ $users->name }}</p>
                                        <p class="text-dark mb-1"><strong>E-Mail Address :</strong>{{ $users->email }}</p>
                                        <p class="text-dark mb-1"><strong>Role :</strong> <span class="badge badge-soft-primary fw-medium ms-2 fs-xs ms-auto">{{ $users->role }}</span></p>
                                        <p class="text-dark mb-1"><strong>Date :</strong>{{ \Carbon\Carbon::parse($users->created_at)->format('d M, Y') }}</p>
                                        <p class="text-dark mb-1"><strong>Status :</strong>{{ $users->status == 0 ? 'Active' : 'Inactive' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- end col -->
                </div><!-- end row -->                 
            </div>
            <!-- container -->
@endsection