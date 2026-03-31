@extends('admin.layouts.index')
@section('title', 'Customer')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">All Customer</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Customer List</li>
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
                                    placeholder="Search Customer Name ...">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>
                            <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
                        </div>


                        <div class="d-flex align-items-center gap-2">
                            <span class="me-2 fw-semibold">Filter By:</span>

                            <!-- Role Type Filter -->
                            <div class="app-search">
                                <select data-table-filter="roles" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Role</option>
                                    <option value="Adminsistrator">Adminsistrator</option>
                                    <option value="Editor">Editor</option>
                                </select>
                                <i data-lucide="shield" class="app-search-icon text-muted"></i>
                            </div>

                            <!-- Status Filter -->
                            <div class="app-search">
                                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                                <i data-lucide="user-check" class="app-search-icon text-muted"></i>
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
                                            id="select-all-files" value="option">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort="customer">Customer Name</th>
                                    <th data-table-sort>E-Mail Address</th>
                                    <th data-table-sort>Join Date</th>
                                    <th data-table-sort data-column="status">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr>
                                    <td class="ps-3"><input
                                            class="form-check-input form-check-input-light fs-14 file-item-check mt-0"
                                            type="checkbox" value="option"></td>
                                    <td>
                                        <h5 class="m-0"><a href="#" class="link-reset">1</a>
                                        </h5>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div>
                                                <h5 class="fs-base mb-0"><a data-sort="user" href="#"
                                                        class="link-reset">Carlos Méndez</a></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>carlos@gmail.com</td>
                                    <td>18 Apr, 2025 <small class="text-muted">9:45 AM</small></td>
                                    <td><span class="badge badge-soft-success text-success badge-label">Active</span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-eye fs-lg"></i></a>
                                            <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-edit fs-lg"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="Customer"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>
                </div>

            </div><!-- end col -->
        </div><!-- end row -->
    </div>
@endsection
