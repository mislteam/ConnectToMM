@extends('admin.layouts.index')
@section('title', 'Joytel Coupon')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }}</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                    <li class="breadcrumb-item active text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }} -
                        Coupons
                    </li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header border-light justify-content-between">
                        <div class="d-flex gap-2">
                            <div class="app-search">
                                <input data-table-search type="search" class="form-control"
                                    placeholder="Search Coupon Code...">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>

                            <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="me-2 fw-semibold">Filter By:</span>

                            <!-- Date Range Filter -->
                            <div class="app-search">
                                <select data-table-filter="product-status" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Status</option>
                                    <option value="Enable">Enable</option>
                                    <option value="Disable">Disable</option>
                                </select>
                                <i data-lucide="box" class="app-search-icon text-muted"></i>
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
                            <div class="d-flex gap-1">
                                <x-create-action menu-text="Create" permission="joytel.coupon.create" :url="route('joytel.coupon.create')"
                                    icon="ti-plus" />
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Code</th>
                                    <th data-table-sort>Percentage</th>
                                    <th data-table-sort>Expired Date</th>
                                    <th data-table-sort data-column="product-status">Status</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr>
                                        <td>
                                            <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                        </td>
                                        <td>
                                            <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $coupon->code }}</h5>
                                        </td>
                                        <td>{{ $coupon->discount_percentage }}</td>

                                        <td> {{ $coupon->expired_date ? \Carbon\Carbon::parse($coupon->expired_date)->format('d M, Y') : '-' }}
                                        </td>

                                        <td data-column="product-status" data-id="{{ $coupon->id }}"
                                            class="{{ $coupon->is_active == 1 ? 'text-success' : 'text-danger' }} fw-semibold joy-toggle-status">
                                            <i class="ti ti-point-filled fs-sm"></i>
                                            {{ $coupon->is_active == 1 ? 'Enable' : 'Disable' }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <x-action-button :url="route('joytel.coupon.show', $coupon->id)" icon="ti-eye" />
                                                <x-action-button :url="route('joytel.coupon.edit', $coupon->id)" icon="ti-edit" />
                                                <x-action-button :data-id="$coupon->id" icon="ti-trash"
                                                    data-url="/joytel/coupon/delete" target-name="coupon-delete"
                                                    class="delete-btn" />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="{{ $settings['joytel_title']->value ?? 'Joytel' }}"
                                id="pagination-info"></div>
                            <div data-table-pagination id="pagination"></div>
                        </div>
                    </div>

                    <!-- coupon delete -->
                    <x-delete-modal-box id="coupon-delete" message="Are you sure you want to delete this coupon?" />

                </div>
            </div>
        </div>
    </div>
@endsection
