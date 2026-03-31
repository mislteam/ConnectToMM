@extends('admin.layouts.index')
@section('title', 'Customer')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">All Orders</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Orders</li>
                </ol>
            </div>
        </div>

        <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 align-items-center g-1">
            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-success rounded-circle fs-22">
                                    <i class="ti ti-check"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">1,240</h3>
                        </div>
                        <p class="mb-0">
                            Completed Orders
                            <span class="float-end badge badge-soft-success">+3.34%</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                    <i class="ti ti-hourglass"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">320</h3>
                        </div>
                        <p class="mb-0">
                            Pending Orders
                            <span class="float-end badge badge-soft-warning">-1.12%</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-danger rounded-circle fs-22">
                                    <i class="ti ti-x"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">87</h3>
                        </div>
                        <p class="mb-0">
                            Canceled Orders
                            <span class="float-end badge badge-soft-danger">-0.75%</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-info rounded-circle fs-22">
                                    <i class="ti ti-shopping-cart"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">540</h3>
                        </div>
                        <p class="mb-0">
                            New Orders
                            <span class="float-end badge badge-soft-info">+4.22%</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                    <i class="ti ti-repeat"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">120</h3>
                        </div>
                        <p class="mb-0">
                            Returned Orders
                            <span class="float-end badge badge-soft-primary">+0.56%</span>
                        </p>
                    </div>
                </div>
            </div>
        </div><!-- end row -->

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header border-light justify-content-between">

                        <div class="d-flex gap-2">
                            <div class="app-search">
                                <input data-table-search type="search" class="form-control" placeholder="Search order...">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>

                            <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="me-2 fw-semibold">Filter By:</span>

                            <!-- Payment Status Filter -->
                            <div class="app-search">
                                <select data-table-filter="payment-status" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Payment Status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Failed">Failed</option>
                                    <option value="Refunded">Refunded</option>
                                </select>
                                <i data-lucide="credit-card" class="app-search-icon text-muted"></i>
                            </div>

                            <!-- Date Range Filter -->
                            <div class="app-search">
                                <select data-table-range-filter="date" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Date Range</option>
                                    <option value="Today">Today</option>
                                    <option value="Last 7 Days">Last 7 Days</option>
                                    <option value="Last 30 Days">Last 30 Days</option>
                                    <option value="This Year">This Year</option>
                                </select>
                                <i data-lucide="calendar" class="app-search-icon text-muted"></i>
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

                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                        <input data-table-select-all
                                            class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox"
                                            value="option">
                                    </th>
                                    <th data-table-sort>Order ID</th>
                                    <th data-table-sort data-column="date">Date</th>
                                    <th data-table-sort="customer">Customer</th>
                                    <th data-table-sort>Product Name</th>
                                    <th data-table-sort>Amount</th>
                                    <th data-table-sort data-column="payment-status">Status</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3">
                                        <input
                                            class="form-check-input form-check-input-light fs-14 product-item-check mt-0"
                                            type="checkbox" value="option">
                                    </td>
                                    <td>
                                        <h5 class="fs-sm mb-0 fw-medium"><a href="#"
                                                class="link-reset">#JOYTEL-20100</a></h5>
                                    </td>
                                    <td>9 May, 2025 <small class="text-muted">10:10 AM</small></td>
                                    <td>
                                        <div class="d-flex justify-content-start align-items-center gap-2">
                                            <div>
                                                <h5 data-sort="customer" class="text-nowrap fs-base mb-0 lh-base">Mason
                                                    Carter</h5>
                                                <p class="text-muted fs-xs mb-0">mason.carter@shopmail.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>eSim-AIS-SIM2FLY399</td>
                                    <td>$129.45</td>
                                    <td class="text-success fw-semibold"><i class="ti ti-point-filled fs-sm"></i> Paid
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-eye fs-lg"></i></a>
                                            <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-edit fs-lg"></i></a>
                                            <a href="#" data-table-delete-row
                                                class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-trash fs-lg"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <input
                                            class="form-check-input form-check-input-light fs-14 product-item-check mt-0"
                                            type="checkbox" value="option">
                                    </td>
                                    <td>
                                        <h5 class="fs-sm mb-0 fw-medium"><a href="#"
                                                class="link-reset">#ROAM-20100</a></h5>
                                    </td>
                                    <td>9 May, 2025 <small class="text-muted">10:10 AM</small></td>
                                    <td>
                                        <div class="d-flex justify-content-start align-items-center gap-2">
                                            <div>
                                                <h5 data-sort="customer" class="text-nowrap fs-base mb-0 lh-base">Mason
                                                    Carter</h5>
                                                <p class="text-muted fs-xs mb-0">mason.carter@shopmail.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>eSim-AIS-SIM2FLY399</td>
                                    <td>$129.45</td>
                                    <td class="text-success fw-semibold"><i class="ti ti-point-filled fs-sm"></i> Paid
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-eye fs-lg"></i></a>
                                            <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-edit fs-lg"></i></a>
                                            <a href="#" data-table-delete-row
                                                class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-trash fs-lg"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="orders"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
