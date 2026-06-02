@extends('admin.layouts.index')
@section('title', 'Coupons')
@section('content')
    <div class="container-fluid">
        @include('components.alert')
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Coupons</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">All Coupons</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="5" class="card">
                    <div class="card-header border-light justify-content-between">
                        <div class="d-flex gap-2">
                            <div class="app-search">
                                <input data-table-search type="search" name="search" class="form-control"
                                    placeholder="Search coupon..." value="{{ request('search') }}">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>
                        </div>
                        </form>

                        <div class="d-flex align-items-center gap-2">
                            <span class="me-2 fw-semibold">Filter By:</span>

                            <!-- Status Filter -->
                            <div class="app-search">
                                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                                <i data-lucide="tag" class="app-search-icon text-muted"></i>
                            </div>

                            <!-- Records Per Page -->
                            <div>
                                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                </select>
                            </div>

                            <a href="{{ route('coupon.create') }}" class="btn btn-primary">Create Coupon</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th data-table-sort>No</th>
                                    <th data-table-sort="coupons">Code no</th>
                                    <th data-table-sort data-column="roles">Amount</th>
                                    <th data-table-sort data-column="roles">Used Count</th>
                                    <th data-table-sort>Expired Date</th>
                                    <th data-table-sort data-column="status">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                @foreach ($coupons as $coupon)
                                    <tr>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start gap-2">
                                                <h5 class="fs-base mb-0"><a data-sort="user" href="#"
                                                        class="link-reset">{{ $coupon->code }}</a></h5>
                                            </div>
                                        </td>
                                        <td>{{ number_format($coupon->coupon_amount) . ' MMK' }}</td>
                                        <td>{{ $coupon->used_count }}</td>

                                        <td> {{ $coupon->expired_date ? \Carbon\Carbon::parse($coupon->expired_date)->format('d M, Y') : '-' }}
                                        </td>
                                        <td><span
                                                class="badge {{ $coupon->is_active == 0 ? 'badge-soft-danger text-danger' : 'badge-soft-success text-success' }} badge-label">{{ $coupon->is_active == 0 ? 'Inactive' : 'Active' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('coupon.show', $coupon->id) }}"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-eye fs-lg"></i></a>
                                                <a href="{{ route('coupon.edit', $coupon->id) }}"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-edit fs-lg"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="coupons">
                                Showing {{ $coupons->firstItem() }} to {{ $coupons->lastItem() }} of
                                {{ $coupons->total() }}
                                entries
                            </div>
                            <div data-table-pagination>
                                {{ $coupons->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
