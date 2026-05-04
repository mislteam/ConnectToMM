@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
@section('content')
    @include('components.alert')
    @if (request()->get('saved'))
        <div class="alert alert-success alert-dismissible fade show alert-fixed" role="alert">
            Saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Joytel</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Joytel - Esim</li>
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
                                    placeholder="Search Product Name...">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>

                            <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="me-2 fw-semibold">Filter By:</span>

                            <!-- Date Range Filter -->
                            <div class="app-search">
                                <select id="product_status" class="form-select form-control my-1 my-md-0">
                                    <option value="All">Status</option>
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
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
                                <a href="{{ route('esim.create') }}" class="btn btn-primary ms-1">
                                    <i class="ti ti-plus fs-sm me-2"></i> Create
                                </a>
                                <a href="#!" class="btn btn-primary ms-1" data-bs-toggle="modal"
                                    data-bs-target="#export_csv">
                                    <i class="ti ti-file-import fs-sm me-2"></i> Import
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="export_csv" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('joytel.import.esim') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Import</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="file" name="file" accept=".xlsx,.xls" required>
                                        @error('file')
                                            <small class="text-danger my-2">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Product Name</th>
                                    <th data-table-sort>Supplier</th>
                                    <th data-table-sort data-column="product-status">Status</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="displayItems">
                                @if ($sim_lists->isNotEmpty())
                                    @foreach ($sim_lists as $index => $esim)
                                        <tr>
                                            <td>
                                                <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                            </td>
                                            <td>
                                                <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $esim->product_name }}</h5>
                                            </td>
                                            <td>{{ $esim->supplier }}</td>

                                            <td
                                                class="{{ $esim->status == 1 ? 'text-success' : 'text-danger' }} fw-semibold">
                                                <i class="ti ti-point-filled fs-sm"></i>
                                                {{ $esim->status == 1 ? 'Enable' : 'Disable' }}
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <div class="btn-group">
                                                        <button type="button"
                                                            class="btn btn-light btn-icon btn-sm rounded-circle"
                                                            data-bs-toggle="dropdown" aria-expanded="false"> <i
                                                                class="ti ti-dots-vertical fs-lg"></i></button>
                                                        <div class="dropdown-menu">
                                                            @php
                                                                $exchangeRates = \App\Models\PriceList::pluck(
                                                                    'exchange_rate',
                                                                    'product_code',
                                                                );
                                                            @endphp
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal" data-bs-target="#manage-price"
                                                                data-plan='@json($esim->plan)'
                                                                data-existing-rates='@json($exchangeRates)'
                                                                data-joytel-id="{{ $esim->id }}">
                                                                <i class="ti ti-currency-dollar fs-lg"></i> Manage Price
                                                            </button>
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal" data-bs-target="#manage-status"
                                                                data-plan='@json($esim->plan)'
                                                                data-id="{{ $esim->id }}">
                                                                <i class="ti ti-box fs-lg"></i> Manage Status
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('esim.edit', $esim->id) }}"
                                                        class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                            class="ti ti-edit fs-lg"></i></a>
                                                    <a href="#" data-id="{{ $esim->id }}"
                                                        data-bs-toggle="modal" data-bs-target="#sim-delete"
                                                        class="btn btn-light btn-icon btn-sm rounded-circle delete-sim-btn">
                                                        <i class="ti ti-trash fs-lg"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Nothing found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="orders" id="pagination-info"></div>
                            <div data-table-pagination id="pagination"></div>
                        </div>
                    </div>

                    <!-- manage price -->
                    <!-- <div class="modal fade" id="manage-price" tabindex="-1" role="dialog"
                                                                                                                                                                                                                                                    aria-labelledby="managePrice" aria-hidden="true">
                                                                                                                                                                                                                                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                                                                                                                                                                                                                        <div class="modal-content">
                                                                                                                                                                                                                                                            <div class="modal-header">
                                                                                                                                                                                                                                                                <h4 class="modal-title" id="managePrice">Manage Price</h4>
                                                                                                                                                                                                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                                                                                                                                                                                                                    aria-label="Close"></button>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                            <div class="modal-body">
                                                                                                                                                                                                                                                                <div class="table-responsive mt-2">
                                                                                                                                                                                                                                                                    <table class="table table-bordered table-nowrap text-center align-middle">
                                                                                                                                                                                                                                                                        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                                                                                                                                                                                                                                            <tr class="text-uppercase fs-xxs">
                                                                                                                                                                                                                                                                                <th>#</th>
                                                                                                                                                                                                                                                                                <th class="text-start">Product SKU</th>
                                                                                                                                                                                                                                                                                <th>Traffic Type</th>
                                                                                                                                                                                                                                                                                <th>Original Selling Price<br>(MMK)</th>
                                                                                                                                                                                                                                                                                <th>Update Selling Price<br>(MMK)</th>
                                                                                                                                                                                                                                                                                <th>Profit<br>(MMK)</th>
                                                                                                                                                                                                                                                                                <th>Increment</th>

                                                                                                                                                                                                                                                                            </tr>
                                                                                                                                                                                                                                                                        </thead>
                                                                                                                                                                                                                                                                        <tbody id="price-invoice-items">

                                                                                                                                                                                                                                                                        </tbody>
                                                                                                                                                                                                                                                                    </table>

                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                            <div class="modal-footer">
                                                                                                                                                                                                                                                                <div class="my-3 d-flex gap-2 justify-content-end">
                                                                                                                                                                                                                                                                    <button type="button" class="btn btn-primary text-end">Update</button>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                </div> -->

                    <!-- manage price -->
                    <div class="modal fade" id="manage-price" tabindex="-1">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Manage Price</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mt-2">
                                        <table class="table table-bordered text-center align-middle"
                                            style="white-space: nowrap; min-width: 800px;">
                                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                <tr>
                                                    <th>#</th>
                                                    <th class="text-start">Product SKU</th>
                                                    <th>Traffic Type</th>
                                                    <th>Exchange Rate</th>
                                                    <th>Portal Price</th>
                                                    <th style="width: 150px;">Selling Rate</th>
                                                    <th>Profit</th>
                                                    <th>Total (MMK)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="price-invoice-items">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <div class="my-3 d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-primary"
                                            id="manage-price-update-btn">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- manage status -->
                    <div class="modal fade" id="manage-status" tabindex="-1" role="dialog"
                        aria-labelledby="manageStatus" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Manage Status</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive mt-2">
                                        <table class="table table-bordered table-nowrap text-center align-middle">
                                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                <tr class="text-uppercase fs-xxs">
                                                    <th>#</th>
                                                    <th class="text-start">Product SKU</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="invoice-items">

                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <div class="my-3 d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-primary text-end">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- sim delete -->
                    <div class="modal fade" id="sim-delete" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <p>Are you sure you want to delete this eSIM?</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.simLists = @json($sim_lists);
        window.additionalPrices = @json($additional_prices);
        window.cnyRate = {{ \App\Models\Currency::where('name', 'cny')->value('value') ?? 0 }};
    </script>
    {{-- @vite('resources/js/manage-joytel.js') --}}
    <script src="{{ asset('assets/js/manage-joytel.js') }}"></script>
@endsection
