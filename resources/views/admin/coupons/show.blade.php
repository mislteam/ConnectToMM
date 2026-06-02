@extends('admin.layouts.index')
@section('title', 'Coupon View')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Coupons</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black"><a href="{{ route('coupon.index') }}">All Coupons</a></li>
                    <li class="breadcrumb-item active text-black">View Coupon</li>
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
                    <div class="card-header d-block p-3">
                        <h4 class="card-title mb-1">Coupon Detail</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="mb-2">
                                    <label class="col-form-label">Coupon Code :</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p>{{ $coupon->code }}</p>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="mb-2">
                                    <label class="col-form-label">Amount :</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p>{{ number_format($coupon->coupon_amount) . ' MMK' }}</p>
                                </div>
                            </div>

                            @if ($coupon->attempt_time != 0)
                                <div class="col-lg-3">
                                    <div class="mb-2">
                                        <label class="col-form-label">Attempt Time :</label>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <p>{{ $coupon->attempt_time . ' times' }}</p>
                                    </div>
                                </div>
                            @endif


                            <div class="col-lg-3">
                                <div class="mb-2">
                                    <label class="col-form-label">Used Count :</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p>{{ $coupon->used_count }}</p>
                                </div>
                            </div>

                            @if ($coupon->expired_date)
                                <div class="col-lg-3">
                                    <div class="mb-2">
                                        <label class="col-form-label">Expired Date :</label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <p>{{ \Carbon\Carbon::parse($coupon->expired_date)->format('d M, Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endif


                            <div class="col-lg-3">
                                <div class="mb-2">
                                    <label class="col-form-label">Coupon Status :</label>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="mb-3">
                                    <p>{{ $coupon->is_active ? 'Active' : 'Inactive' }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
