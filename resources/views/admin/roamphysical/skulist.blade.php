@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">FiROAM</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">SKU List</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#global-tab">
                                FiROAM GLOBAL
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#asia-tab">
                                FiROAM ASIA
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="global-tab">
                            <div data-table="global" data-table-rows-per-page="8">
                                <div class="card-header border-light justify-content-between">

                                    <div class="d-flex gap-2">
                                        <div class="app-search">
                                            <input data-table-search="global" type="search" class="form-control"
                                                placeholder="Search Country Name ...">
                                            <i data-lucide="search" class="app-search-icon text-muted"></i>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="me-2 fw-semibold">Filter By:</span>

                                        <!-- Date Range Filter -->
                                        <div class="app-search">
                                            <select data-table-range-filter="product-status"
                                                class="form-select form-control my-1 my-md-0">
                                                <option value="All">Status</option>
                                                <option value="Enable">Enable</option>
                                                <option value="Disable">Disable</option>
                                            </select>
                                            <i data-lucide="box" class="app-search-icon text-muted"></i>
                                        </div>

                                        <!-- Records Per Page -->
                                        <div>
                                            <select data-table-set-rows-per-page="global"
                                                class="form-select form-control my-1 my-md-0">
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
                                                        class="form-check-input form-check-input-light fs-14 mt-0"
                                                        type="checkbox" value="option">
                                                </th>
                                                <th data-table-sort>No</th>
                                                <th data-table-sort>SKU ID</th>
                                                <th data-table-sort>Courntry Name</th>
                                                <th data-table-sort>Courntry Code</th>
                                                <th data-table-sort data-column="product-status">Status</th>
                                                <th class="text-center" style="width: 1%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roamGlobal as $index => $item)
                                                <tr>
                                                    <td class="ps-3">
                                                        <input
                                                            class="form-check-input form-check-input-light fs-14 product-item-check mt-0"
                                                            type="checkbox" value="option">
                                                    </td>
                                                    <td>
                                                        <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                                    </td>
                                                    <td>
                                                        <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $item->sku_id }}
                                                        </h5>
                                                    </td>
                                                    <td>
                                                        <h5 class="text-nowrap fs-base mb-0 lh-base">
                                                            {{ $item->country_name }}</h5>
                                                    </td>
                                                    <td>
                                                        <h5 class="text-nowrap fs-base mb-0 lh-base">
                                                            {{ $item->country_code }}</h5>
                                                    </td>
                                                    <td
                                                        class="{{ $item->status ? 'text-success' : 'text-danger' }} fw-semibold">
                                                        <i class="ti ti-point-filled fs-sm"></i>
                                                        {{ $item->status ? 'Enable' : 'Disable' }}
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch fs-xxl mb-2">
                                                            <input type="checkbox" class="form-check-input toggle-status"
                                                                data-id="{{ $item->sku_id }}"
                                                                {{ $item->status ? 'checked' : '' }}>
                                                            <label class="form-check-label fs-base">
                                                                {{ $item->status ? 'Enable' : 'Disable' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div data-table-pagination-info="global"></div>
                                        <div data-table-pagination ="global"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="asia-tab">
                            <div data-table="asia" data-table-rows-per-page="8">
                                <div class="card-header border-light justify-content-between">

                                    <div class="d-flex gap-2">
                                        <div class="app-search">
                                            <input data-table-search="asia" type="search" class="form-control"
                                                placeholder="Search Country Name ...">
                                            <i data-lucide="search" class="app-search-icon text-muted"></i>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="me-2 fw-semibold">Filter By:</span>

                                        <!-- Date Range Filter -->
                                        <div class="app-search">
                                            <select data-table-range-filter="product-status"
                                                class="form-select form-control my-1 my-md-0">
                                                <option value="All">Status</option>
                                                <option value="Enable">Enable</option>
                                                <option value="Disable">Disable</option>
                                            </select>
                                            <i data-lucide="box" class="app-search-icon text-muted"></i>
                                        </div>

                                        <!-- Records Per Page -->
                                        <div>
                                            <select data-table-set-rows-per-page="asia"
                                                class="form-select form-control my-1 my-md-0">
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
                                                        class="form-check-input form-check-input-light fs-14 mt-0"
                                                        type="checkbox" value="option">
                                                </th>
                                                <th data-table-sort>No</th>
                                                <th data-table-sort>SKU ID</th>
                                                <th data-table-sort>Courntry Name</th>
                                                <th data-table-sort>Courntry Code</th>
                                                <th data-table-sort data-column="product-status">Status</th>
                                                <th class="text-center" style="width: 1%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roamAsia as $index => $item)
                                                <tr>
                                                    <td class="ps-3">
                                                        <input
                                                            class="form-check-input form-check-input-light fs-14 product-item-check mt-0"
                                                            type="checkbox" value="option">
                                                    </td>
                                                    <td>
                                                        <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                                    </td>
                                                    <td>
                                                        <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $item->sku_id }}
                                                        </h5>
                                                    </td>
                                                    <td>
                                                        <h5 class="text-nowrap fs-base mb-0 lh-base">
                                                            {{ $item->country_name }}</h5>
                                                    </td>
                                                    <td>
                                                        <h5 class="text-nowrap fs-base mb-0 lh-base">
                                                            {{ $item->country_code }}</h5>
                                                    </td>
                                                    <td
                                                        class="{{ $item->status ? 'text-success' : 'text-danger' }} fw-semibold">
                                                        <i class="ti ti-point-filled fs-sm"></i>
                                                        {{ $item->status ? 'Enable' : 'Disable' }}
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch fs-xxl mb-2">
                                                            <input type="checkbox" class="form-check-input toggle-status"
                                                                data-id="{{ $item->sku_id }}"
                                                                {{ $item->status ? 'checked' : '' }}>
                                                            <label class="form-check-label fs-base">
                                                                {{ $item->status ? 'Enable' : 'Disable' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div data-table-pagination-info="asia"></div>
                                        <div data-table-pagination="asia"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end col -->
        </div><!-- end row -->

    </div>
    <script>
        document.querySelectorAll('.toggle-status').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const skuId = this.getAttribute('data-id');
                fetch(`/roam/roam-physicalskus/toggle-status/${skuId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            location.reload(); // Reload to update label & color
                        }
                    });
            });
        });
    </script>

@endsection
