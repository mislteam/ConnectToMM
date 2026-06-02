@extends('admin.layouts.index')
@section('title', 'Coupon Create')
@section('content')
    <style>
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 250px;
        }
    </style>
    <div class="container-fluid">
        <div id="live-alert-container"></div>
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Coupons</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black"><a href="{{ route('coupon.index') }}">All Coupons</a></li>
                    <li class="breadcrumb-item active text-black">Create Coupon</li>
                </ol>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <a href="{{ route('coupon.index') }}" class="btn btn-primary ms-1">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title text-black">Coupon Information</h4>
                    </div> <!-- end card-header -->

                    <div class="card-body">
                        <form action="{{ route('coupon.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Coupon Code <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="text" name="code" class="form-control"
                                            placeholder="Enter Coupon Code" required="">
                                        @error('code')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Amount (MMK) <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="number" name="amount" class="form-control"
                                            placeholder="Enter Amount (MMK)" required="">
                                        @error('amount')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Attempt Time <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="number" name="attempt_time" class="form-control"
                                            placeholder="Enter Attempt Time">
                                        @error('attempt_time')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Expired Date <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="date" name="expired_date" class="form-control"
                                            placeholder="Enter Expired Date">
                                        @error('expired_date')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Create Coupon</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
