@extends('admin.layouts.index')
@section('title', 'Coupon Edit')
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
                    <li class="breadcrumb-item active text-black">Edit Coupon</li>
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
                    </div>

                    <div class="card-body">
                        <form action="{{ route('roam.coupon.update', $coupon->id) }}" method="POST">
                            @csrf
                            @method('patch')
                            <x-form-input label="Coupon Code" name="code" placeholder="Enter Coupon Code"
                                :isrequired="true" :value="$coupon->code" />

                            <div class="form-group row mb-3">
                                <label for="category" class="form-label col-sm-2">Plan<span
                                        class="text-danger">*</span></label>

                                <div class="app-search col-sm-10">
                                    <select class="select2_design form-select form-control my-1 my-md-0" multiple="multiple"
                                        name="plans[]" id="choose__plan" data-placeholder="Choose Plans">
                                        <option value="All" {{ in_array('All', $coupon->plans) ? 'selected' : '' }}>All
                                        </option>
                                        @foreach ($skus as $sku)
                                            <option value="{{ $sku }}"
                                                {{ in_array($sku, $coupon->plans) ? 'selected' : '' }}>
                                                {{ $sku }}</option>
                                        @endforeach
                                    </select>
                                    @error('plans[]')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <x-form-input type="number" label="Discount Percentage" name="discount_percentage"
                                placeholder="Enter Discount Percentage" :value="(int) $coupon->discount_percentage" :isrequired="true" />

                            <x-form-input type="number" label="Attempt Time" name="attempt_time"
                                placeholder="Enter Attempt Time" :value="$coupon->attempt_time" />

                            <x-form-input type="date" label="Expired Date" name="expired_date"
                                placeholder="Enter Expired Date" :value="$coupon->expired_date
                                    ? \Carbon\Carbon::parse($coupon->expired_date)->format('Y-m-d')
                                    : ''" />

                            <div class="form-group row mb-3">
                                <div class="col-sm-2">
                                    <div class="mb-3">
                                        <label class="col-form-label">Status <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-10">
                                    <div class="mb-3">
                                        <select name="status" required class="form-control">
                                            <option value="">Select Status</option>
                                            <option value="1" {{ $coupon->is_active ? 'selected' : '' }}>Active
                                            </option>
                                            <option value="0" {{ !$coupon->is_active ? 'selected' : '' }}>Inactive
                                            </option>
                                        </select>
                                        @error('status')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Update Coupon</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
