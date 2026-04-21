@php
    $newSkus = $newSkus ?? [];
    $updatedSkus = $updatedSkus ?? [];
    $newPackages = $newPackages ?? [];
    $updatedPackages = $updatedPackages ?? [];
@endphp
@extends('admin.layouts.index')
@section('title', 'Update Data')
@section('content')
    @include('components.alert')
    <style>
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #ddd;
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <div id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Update Data</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Update Data</li>
                </ol>
            </div>
        </div>

        @if (!empty($syncReport))
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm mb-0">
                        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                            <div><strong>Last sync:</strong> {{ $syncReport['synced_at'] ?? '-' }}</div>
                            <div><strong>New SKUs:</strong> {{ $syncReport['new_skus'] ?? 0 }}</div>
                            <div><strong>Updated SKUs:</strong> {{ $syncReport['updated_skus'] ?? 0 }}</div>
                            <div><strong>New Packages:</strong> {{ $syncReport['new_packages'] ?? 0 }}</div>
                            <div><strong>Updated Packages:</strong> {{ $syncReport['updated_packages'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="20" class="card">
                    <div class="card-header fw-semibold">New Physical SKU Data</div>
                    <div class="table-responsive mb-5">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>No</th>
                                    <th>DP Name</th>
                                    <th>SKU ID</th>
                                    <th>Country Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($newSkus as $sku)
                                    <tr class="table-success">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sku->dp_name ?? ($sku['dp_name'] ?? '-') }}</td>
                                        <td>{{ $sku->sku_id ?? ($sku['sku_id'] ?? '-') }}</td>
                                        <td>{{ $sku->country_name ?? ($sku['country_name'] ?? '-') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No new SKUs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="20" class="card">
                    <div class="card-header fw-semibold">Updated Physical SKU Data</div>
                    <div class="table-responsive mb-5">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>No</th>
                                    <th>DP Name</th>
                                    <th>Old Country</th>
                                    <th>New Country</th>
                                    <th>Changed Fields</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($updatedSkus as $sku)
                                    <tr class="table-warning">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sku['dp_id'] ?? ($sku['after']['dp_name'] ?? '-') }}</td>
                                        <td>{{ $sku['before']['country_name'] ?? '-' }}</td>
                                        <td>{{ $sku['after']['country_name'] ?? '-' }}</td>
                                        <td>
                                            @forelse(($sku['changed_keys'] ?? []) as $key)
                                                <span class="badge bg-warning text-dark me-1">{{ $key }}</span>
                                            @empty
                                                <span class="text-muted">No changes</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No updated SKUs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="50" class="card">
                    <div class="card-header fw-semibold">New Physical Package Data</div>
                    <div class="table-responsive mb-5">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>No</th>
                                    <th>DP Name</th>
                                    <th>Pkg Pid</th>
                                    <th>Package Plan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($newPackages as $pkg)
                                    <tr class="table-success">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $pkg['dp_name'] ?? '-' }}</td>
                                        <td>{{ $pkg['pid'] ?? '-' }}</td>
                                        <td>
                                            @php
                                                $showName = $pkg['showName'] ?? '';
                                                $planLabel = 'Fixed : ' . ($pkg['days'] ?? '-') . 'days';
                                                if (stripos($showName, 'Unlimited') !== false) {
                                                    $planLabel = 'Unilimited';
                                                } elseif (
                                                    stripos($showName, 'DayPass') !== false ||
                                                    stripos($showName, 'Daypass') !== false
                                                ) {
                                                    $planLabel = 'Daypass : ' . ($pkg['days'] ?? '-') . 'days';
                                                }
                                            @endphp
                                            {{ $pkg['flows'] ?? '' }} {{ $pkg['unit'] ?? '' }} : {{ $planLabel }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No new packages found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="50" class="card">
                    <div class="card-header fw-semibold">Updated Physical Package Data</div>
                    <div class="table-responsive mb-5">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>No</th>
                                    <th>Pkg Pid</th>
                                    <th>Old Plan</th>
                                    <th>New Plan</th>
                                    <th>Changed Fields</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($updatedPackages as $pkg)
                                    @php
                                        $old = $pkg['before'] ?? [];
                                        $new = $pkg['after'] ?? [];
                                    @endphp
                                    <tr class="table-warning">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $pkg['pid'] ?? '-' }}</td>
                                        <td>{{ $old['showName'] ?? '-' }}</td>
                                        <td>{{ $new['showName'] ?? '-' }}</td>
                                        <td>
                                            @forelse(($pkg['changed_keys'] ?? []) as $key)
                                                <span class="badge bg-warning text-dark me-1">{{ $key }}</span>
                                            @empty
                                                <span class="text-muted">No changes</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No updated packages found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('roamphysical.SkuPackages') }}" id="syncBtn" class="btn btn-primary text-end"> Sync... </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const syncButton = document.querySelector('#syncBtn');

            if (syncButton) {
                syncButton.addEventListener('click', function() {
                    document.getElementById('loadingOverlay').style.display = 'flex';
                });
            }
        });
    </script>
@endsection
