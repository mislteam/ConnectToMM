@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }}</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">{{ $settings['joytel_title']->value ?? 'Joytel' }} - Region
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
                                    placeholder="Search Country Name...">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>
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
                                <a href="{{ route('region.create') }}" class="btn btn-primary ms-1">Create</a>
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
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Usage Location</th>
                                    <th data-table-sort data-column="product-status">Status</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="displayItems">
                                @if ($region_lists->isNotEmpty())
                                    @foreach ($region_lists as $region)
                                        <tr class="item">
                                            <td class="ps-3">
                                                <input
                                                    class="form-check-input form-check-input-light fs-14 product-item-check mt-0"
                                                    type="checkbox" value="option">
                                            </td>
                                            <td>
                                                <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                            </td>
                                            <td>{{ $region->location ?? '' }}</td>
                                            <td
                                                class="{{ $region->status == 1 ? 'text-success' : 'text-danger' }} fw-semibold">
                                                <i class="ti ti-point-filled fs-sm"></i>
                                                {{ $region->status == 1 ? 'Enable' : 'Disable' }}
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('region.edit', $region->id) }}"
                                                        class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                            class="ti ti-edit fs-lg"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="ps-3">
                                        <td colspan="4" class="text-center">Nothing Found.</td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="countries" id="pagination-info"></div>
                            <div data-table-pagination id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectBox = document.getElementById('product_status');
            const showBox = document.getElementById('displayItems');
            const defaultShowBox = showBox.innerHTML;
            const items = @json($region_lists);

            selectBox.addEventListener('change', function() {
                const value = selectBox.value;
                const displayItems = items.filter(item => item.status == value);

                // Clear table body
                showBox.innerHTML = '';
                if (displayItems.length > 0) {
                    displayItems.forEach((location, index) => {
                        const statusClass = location.status == 1 ? 'text-success' : 'text-danger';
                        const statusText = location.status == 1 ? 'Enable' : 'Disable';

                        const row = `
                   <tr>
                        <td class="ps-3">
                            <input class="form-check-input form-check-input-light fs-14 product-item-check mt-0" type="checkbox" value="option">
                        </td>
                        <td>
                            <h5 class="fs-sm mb-0 fw-medium">${index+1}</h5>
                        </td>
                        <td>${location.location}</td>
                        <td class="${statusClass} fw-semibold">
                            <i class="ti ti-point-filled fs-sm"></i>
                            ${statusText}
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a href="/region/edit/${location.id}" class="btn btn-light btn-icon btn-sm rounded-circle">
                                    <i class="ti ti-edit fs-lg"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                `;
                        showBox.insertAdjacentHTML('beforeend', row);
                    });
                } else if (value == 'All') {
                    showBox.innerHTML = defaultShowBox;
                } else {
                    showBox.innerHTML = `<tr><td colspan="6" class="text-center">Nothing found.</td></tr>`;
                }
            });
        });
    </script>
@endsection
