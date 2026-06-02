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
    </style>
    <div class="container-fluid">
        <div id="live-alert-container"></div>
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Coupons</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black"><a href="{{ route('coupon.index') }}">All Coupons</a></li>
                    <li class="breadcrumb-item active text-black">Edit Coupon</li>
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
                        <form action="{{ route('coupon.update', $coupon->id) }}" method="POST">
                            @csrf
                            @method('patch')
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Coupon Code <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="text" name="code" class="form-control"
                                            value="{{ old('coupon', $coupon->code) }}" placeholder="Enter Coupon Code"
                                            required="">
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
                                            placeholder="Enter Amount (MMK)" required=""
                                            value="{{ old('amount', $coupon->coupon_amount) }}">
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
                                            placeholder="Enter Attempt Time"
                                            value="{{ $coupon->attempt_time != 0 ? old('attempt_time', $coupon->attempt_time) : '' }}">
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
                                            placeholder="Enter Expired Date"
                                            value="{{ $coupon->expired_date ? old('expired_date', \Carbon\Carbon::parse($coupon->expired_date)->format('Y-m-d')) : old('expired_date') }}">
                                        @error('expired_date')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Status <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
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
    <script>
        const expiredDateEl = document.querySelector('input[name="expired_date"]');
        const statusEl = document.querySelector('select[name="status"]');
        const liveAlertContainer = document.getElementById('live-alert-container');
        const oldStatusVal = statusEl.value;

        liveAlertContainer.innerHTML = "";

        if (expiredDateEl && statusEl) {
            const today = new Date();
            today.setHours(0, 0, 0, 0)
            expiredDateEl.addEventListener('change', function() {
                const expiredDate = new Date(expiredDateEl.value);
                expiredDate.setHours(0, 0, 0, 0);

                if (expiredDate.getTime() <= today.getTime()) {
                    statusEl.value = '0';

                    const alert = document.createElement('div');

                    alert.className =
                        'alert alert-danger alert-dismissible fade show alert-fixed';

                    alert.setAttribute('role', 'alert');
                    alert.setAttribute('data-auto-dismiss', '5000');

                    alert.innerHTML = `
                    Expired date is today or yesterday. Status changed to Inactive.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                    liveAlertContainer.appendChild(alert);
                } else {
                    statusEl.value = oldStatusVal;
                    liveAlertContainer.innerHTML = "";
                }
            });
        }
    </script>
@endsection
