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

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            color: #fff !important;
        }
    </style>
    <div class="container-fluid">
        <div id="live-alert-container"></div>
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Coupons</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                    <li class="breadcrumb-item active text-black"><a
                            href="{{ route('roam.coupon.index') }}">{{ $settings['roam_title']->value . ' Coupons' }}</a>
                    </li>
                    <li class="breadcrumb-item active text-black">Create Coupon</li>
                </ol>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <a href="{{ route('roam.coupon.index') }}" class="btn btn-primary ms-1">Back</a>
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
                        <form action="{{ route('roam.coupon.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <x-form-input label="Coupon Code" name="code" placeholder="Enter Coupon Code"
                                    :isrequired="true" />

                                <div class="form-group row mb-3">
                                    <label for="category" class="form-label col-sm-2">Plan<span
                                            class="text-danger">*</span></label>

                                    <div class="app-search col-sm-10">
                                        <select class="select2_design form-select form-control my-1 my-md-0"
                                            data-placeholder="Choose Plans" multiple="multiple" name="plans[]"
                                            id="choose__plan">
                                            <option value="All">All</option>
                                            @foreach ($skus as $sku)
                                                <option value="{{ $sku }}">
                                                    {{ $sku }}</option>
                                            @endforeach
                                        </select>
                                        @error('plans[]')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <x-form-input type="number" label="Discount Percentage" name="discount_percentage"
                                    placeholder="Enter Discount Percentage" :isrequired="true" />

                                <x-form-input type="number" label="Attempt Time" name="attempt_time"
                                    placeholder="Enter Attempt Time" />

                                <x-form-input type="date" label="Expired Date" name="expired_date"
                                    placeholder="Enter Expired Date" />
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
