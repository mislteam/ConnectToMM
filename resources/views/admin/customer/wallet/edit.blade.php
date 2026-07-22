@extends('admin.layouts.index')
@section('title', 'Customer Wallet')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 customer-page-title">Wallet Setting</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"
                            class="customer-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.index') }}"
                            class="customer-breadcrumb-link">Customers</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.wallet.index') }}"
                            class="customer-breadcrumb-link">Hnin
                            Shwe Sin - Wallet Setting</a></li>
                    <li class="breadcrumb-item customer-breadcrumb-current active">Edit</li>
                </ol>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('customer.wallet.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="row col-12">
            <div class="d-flex gap-2 align-items-start">
                <div class="card col-lg-8">
                    <div class="card-header d-block">
                        <h4 class="card-title text-black">Wallet Information</h4>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 mb-3">
                                    <label class="col-form-label">Name:</label>
                                </div>
                                <div class="col-lg-9 mb-3">
                                    <label class="col-form-label">Hnin Shwe Sin</label>
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label class="col-form-label">Email:</label>
                                </div>
                                <div class="col-lg-9 mb-3">
                                    <label class="col-form-label">citypyay460@gmail.com</label>
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label class="col-form-label">Remaining Balance:</label>
                                </div>
                                <div class="col-lg-9 mb-3">
                                    <label class="col-form-label">50,000 MMK</label>
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label class="col-form-label">Pending Date:</label>
                                </div>
                                <div class="col-lg-9 mb-3">
                                    <label class="col-form-label">8 July, 2026</label>
                                </div>

                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Top Up Balance <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="text" name="name" class="form-control" value="" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Top Up</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card col-lg-4">
                    <div class="card-header d-block">
                        <h4 class="card-title text-black">Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="app-search">
                            <select class="form-select form-control" id="statusOne" name="status" required>
                                <option value="1">Enable
                                </option>
                                <option value="0">Disable
                                </option>
                            </select>
                            <i data-lucide="toggle-left" class="app-search-icon text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
