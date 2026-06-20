@extends('admin.layouts.index')
@section('title', 'All Customer')
@section('content')
    <style>
        .customer-detail-page-title,
        .customer-detail-breadcrumb-current {
            color: #111827;
        }

        .customer-detail-breadcrumb-link {
            color: #4b5563;
        }

        .customer-detail-breadcrumb-link:hover {
            color: #1f2937;
        }

        html[data-bs-theme="dark"] .customer-detail-page-title,
        html[data-bs-theme="dark"] .customer-detail-breadcrumb-current {
            color: #e5edf9;
        }

        html[data-bs-theme="dark"] .customer-detail-breadcrumb-link {
            color: #9fb1cc;
        }

        html[data-bs-theme="dark"] .customer-detail-breadcrumb-link:hover {
            color: #dbe7ff;
        }

        .customer-account-title {
            color: #111827;
        }

        .customer-account-text {
            color: #1f2937;
        }

        html[data-bs-theme="dark"] .customer-account-title {
            color: #e5edf9;
        }

        html[data-bs-theme="dark"] .customer-account-text {
            color: #c9d7ee;
        }
    </style>
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 customer-detail-page-title">Profile</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);"
                            class="customer-detail-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item active customer-detail-breadcrumb-current">All Customers</li>
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
                        <h4 class="card-title mb-1 customer-account-title">Account Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-start align-items-center gap-3">
                            <div class="avatar avatar-xxl">
                                <img src="{{ $customer->profile_image ? asset('storage/profile_images/' . $customer->profile_image) : asset('assets/images/user-3.jpg') }}"
                                    alt="avatar-2" class="img-fluid rounded-circle">
                            </div>
                            <div>
                                <p class="customer-account-text mb-1"><strong>Name : </strong>{{ $customer->name }}</p>
                                <p class="customer-account-text mb-1"><strong>E-Mail Address :
                                    </strong>{{ $customer->email }}</p>
                                <p class="customer-account-text mb-1"><strong>Login Method
                                        :</strong>{{ $customer->auth_provider }}</p>
                                <p class="customer-account-text mb-1"><strong>Date
                                        : </strong>{{ \Carbon\Carbon::parse($customer->created_at)->format('d M, Y') }}</p>
                                <p class="customer-account-text mb-1"><strong>Status
                                        : </strong>{{ $customer->status == 0 ? 'Active' : 'Inactive' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end col -->
        </div><!-- end row -->
    </div>
    <!-- container -->
@endsection
