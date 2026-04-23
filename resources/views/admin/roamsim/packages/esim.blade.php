@extends('admin.layouts.index')
@section('title', 'Physical Sim')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Roam</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Roam - Esim</li>
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
                                    placeholder="Search Plan Name...">
                                <i data-lucide="search" class="app-search-icon text-muted"></i>
                            </div>

                            <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
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
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Plan Name</th>
                                    <th data-table-sort data-column="product-status">Status</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($packages as $index => $pkg)
                                    @php
                                        $plans = $pkg->roam;
                                        $hasPlans = !empty($plans?->packages);
                                    @endphp
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
                                            <h5 class="text-nowrap fs-base mb-0 lh-base">
                                                {{ $pkg->country_name ?? 'No Title' }}</h5>
                                            @if (!$hasPlans)
                                                <small class="text-muted d-block">Package data not synced yet</small>
                                            @endif
                                        </td>
                                        <td class="{{ $pkg->status == 1 ? 'text-success' : 'text-danger' }} fw-semibold">
                                            <i class="ti ti-point-filled fs-sm"></i>
                                            {{ $pkg->status == 1 ? 'Enable' : 'Disable' }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <div class="btn-group">
                                                    <button type="button"
                                                        class="btn btn-light btn-icon btn-sm rounded-circle"
                                                        data-bs-toggle="dropdown" aria-expanded="false"> <i
                                                            class="ti ti-dots-vertical fs-lg"></i></button>
                                                    <div class="dropdown-menu">
                                                        <!-- Dynamic modal target using SKU ID -->
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#manage-price-{{ $index }}">
                                                            <i class="ti ti-currency-dollar fs-lg"></i> Manage Price
                                                        </button>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#manage-status-{{ $index }}">
                                                            <i class="ti ti-box fs-lg"></i> Manage Status
                                                        </button>
                                                    </div>
                                                </div>
                                                <a href="{{ route('roamEsimEdit', ['skuid' => $pkg['sku_id']]) }}"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-edit fs-lg"></i></a>
                                                <a href="#" data-table-delete-row
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-trash fs-lg"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="orders"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>

                    @foreach ($packages as $index => $pkg)
                        @php
                            $plans = $pkg->roam;
                        @endphp
                        <div class="modal fade" id="manage-price-{{ $index }}" tabindex="-1" role="dialog"
                            aria-labelledby="managePrice{{ $index }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="managePrice">Manage Price</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mt-2">
                                            <form action="{{ route('pricelist.store') }}" method="POST">
                                                @csrf

                                                <table class="table table-bordered table-nowrap text-center align-middle">
                                                    <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                        <tr class="text-uppercase fs-xxs">
                                                            <th>#</th>
                                                            <th class="text-start">Plan Name</th>
                                                            <th>Exchange Rate</th>
                                                            <th>Portal Price</th>
                                                            <th style="width: 150px;">Selling Rate</th>
                                                            <th>Profit</th>
                                                            <th>Total(MMK)</th>
                                                        </tr>
                                                    </thead>
                                                    @if (!empty($plans->packages))
                                                        @foreach ($plans->packages as $innerIndex => $plan)
                                                            @php
                                                                $base_price = $plan['price'] ?? 0;
                                                                $open_card_fee = $plan['openCardFee'] ?? 0;
                                                                $portal_price = $base_price + $open_card_fee;

                                                                $savedRate = App\Models\PriceList::where(
                                                                    'product_code',
                                                                    $plan['priceid'],
                                                                )
                                                                    ->where('dp_status', 0)
                                                                    ->first();

                                                                $exchange_rate = round(
                                                                    $portal_price * $usd_exchange_rate,
                                                                    2,
                                                                );

                                                                // saved selling rate from DB exchange_rate column
                                                                $selling_rate = $savedRate->exchange_rate ?? 0;

                                                                $total = round($portal_price * $selling_rate);

                                                                $profit = round($total - $exchange_rate);

                                                            @endphp
                                                            <tbody id="invoice-items">
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td class="text-start">{{ $plan['flows'] ?? '' }}
                                                                        {{ $plan['unit'] ?? '' }} :
                                                                        @php
                                                                            $name = $plan['showName'] ?? '';
                                                                        @endphp

                                                                        @if (stripos($name, 'Unlimited') !== false)
                                                                            {{-- Priority 1: Catches 'Unlimited' or 'unlimited' --}}
                                                                            Unlimited : {{ $plan['days'] }} days
                                                                        @elseif(stripos($name, 'Daypass') !== false)
                                                                            {{-- Priority 2: Catches 'DayPass', 'Daypass', 'daypass', etc. --}}
                                                                            DayPass : {{ $plan['days'] }} days
                                                                        @else
                                                                            {{-- Priority 3: Fallback for '1GB/3days', etc. --}}
                                                                            Fixed : {{ $plan['days'] }} days
                                                                        @endif
                                                                    </td>
                                                                    <td><label
                                                                            class="form-label">{{ $exchange_rate }}</label>
                                                                    </td>

                                                                    <td><label
                                                                            class="form-label">{{ $portal_price }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            class="form-control exchange-input"
                                                                            name="plans[{{ $innerIndex }}][selling_rate]"
                                                                            value="{{ $selling_rate }}" step="0.01">
                                                                    </td>
                                                                    <td><label
                                                                            class="form-label profit-label">{{ $profit }}</label>
                                                                    </td>
                                                                    <td><label
                                                                            class="form-label total-label">{{ $total }}</label>
                                                                    </td>
                                                                    <input type="hidden"
                                                                        name="plans[{{ $innerIndex }}][profit]"
                                                                        class="profit-input" value="{{ $profit }}">
                                                                    <input type="hidden"
                                                                        name="plans[{{ $innerIndex }}][sku_id]"
                                                                        value="{{ $pkg->sku_id }}">
                                                                    <input type="hidden"
                                                                        name="plans[{{ $innerIndex }}][priceid]"
                                                                        value="{{ $plan['priceid'] }}">
                                                                    <input type="hidden" class="portal-price"
                                                                        value="{{ $portal_price }}">
                                                                    <input type="hidden" class="base-exchange-rate"
                                                                        value="{{ $exchange_rate }}">
                                                                </tr>
                                                            </tbody>
                                                        @endforeach
                                                    @else
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="3" class="text-center">No plans
                                                                    available
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    @endif
                                                </table>

                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary text-end">Update</button>
                                        </div>
                                    </div>
                                    </form>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    @endforeach


                    @foreach ($packages as $index => $pkg)
                        <div class="modal fade" id="manage-status-{{ $index }}" tabindex="-1" role="dialog"
                            aria-labelledby="manageStatusLabel{{ $index }}" aria-hidden="true">
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
                                                        <th class="text-start">Plan Name</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>

                                                @php
                                                    $plans = App\Models\Roam::where('sku_id', $pkg->sku_id)->first();

                                                @endphp
                                                @if (!empty($plans->packages))
                                                    @foreach ($plans->packages as $index => $plan)
                                                        <tbody id="invoice-items">
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td class="text-start">{{ $plan['flows'] ?? '' }}
                                                                    {{ $plan['unit'] ?? '' }} :
                                                                    @php
                                                                        $name = $plan['showName'] ?? '';
                                                                    @endphp

                                                                    @if (stripos($name, 'Unlimited') !== false)
                                                                        {{-- Priority 1: Catches 'Unlimited' or 'unlimited' --}}
                                                                        Unlimited : {{ $plan['days'] }} days
                                                                    @elseif(stripos($name, 'Daypass') !== false)
                                                                        {{-- Priority 2: Catches 'DayPass', 'Daypass', 'daypass', etc. --}}
                                                                        DayPass : {{ $plan['days'] }} days
                                                                    @else
                                                                        {{-- Priority 3: Fallback for '1GB/3days', etc. --}}
                                                                        Fixed : {{ $plan['days'] }} days
                                                                    @endif
                                                                </td>
                                                                <td>

                                                                    <form action="{{ route('roam.updatePackageStatus') }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        <input type="hidden"
                                                                            name="plans[{{ $index }}][sku_id]"
                                                                            value="{{ $pkg->sku_id }}">
                                                                        <input type="hidden"
                                                                            name="plans[{{ $index }}][priceid]"
                                                                            value="{{ $plan['priceid'] }}">
                                                                        <input type="hidden"
                                                                            name="plans[{{ $index }}][plan_name]"
                                                                            value="{{ $plan['flows'] ?? '' }} {{ $plan['unit'] ?? '' }}">
                                                                        <input type="hidden" name="status"
                                                                            value="0">

                                                                        <div
                                                                            class="form-check form-switch form-check-secondary fs-xxl mb-2">
                                                                            <input type="checkbox"
                                                                                class="form-check-input" name="status"
                                                                                value="1"
                                                                                onchange="this.form.submit()"
                                                                                {{ ($plan['status'] ?? 0) == 1 ? 'checked' : '' }}>
                                                                            <label
                                                                                class="form-check-label fs-base {{ ($plan['status'] ?? 0) == 1 ? 'text-success' : 'text-danger' }}">
                                                                                {{ ($plan['status'] ?? 0) == 1 ? 'Enable' : 'Disable' }}
                                                                            </label>
                                                                        </div>
                                                                    </form>

                                                                </td>
                                                            </tr>

                                                        </tbody>
                                                    @endforeach
                                                @else
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No plans available</td>
                                                        </tr>
                                                    </tbody>
                                                @endif

                                            </table>

                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    @endforeach
                </div>

            </div><!-- end col -->
        </div><!-- end row -->

    </div>
    <!-- <script>
        document.addEventListener("input", function(e) {
            if (e.target.classList.contains("price-input")) {
                let row = e.target.closest("tr");

                let userPrice = parseFloat(e.target.value) || 0;
                console.log(userPrice);


                let originalPrice = parseFloat(row.querySelector(".original-price")?.value || 0);
                console.log(originalPrice);

                let profit = userPrice - originalPrice;
                console.log(profit);

                let profitInput = row.querySelector(".profit-input");
                if (profitInput) {
                    // profitInput.value = profit.toFixed(2);
                    profitInput.value = profit;
                }


            }
        });
    </script> -->

    <!-- <script>
        document.addEventListener("input", function(e) {
            if (e.target.classList.contains("price-input")) {
                let row = e.target.closest("tr");

                let mmk = parseFloat(row.querySelector(".mmk-price").value) || 0;
                let extra = parseFloat(e.target.value) || 0;
                let total = mmk + extra;

                row.querySelector(".total-input").value = total + 'MMK';
            }
        });
    </script> -->

    {{-- <script>
        document.addEventListener("input", function(e) {

            if (!e.target.classList.contains("exchange-input")) return;

            let row = e.target.closest("tr");

            let portalPrice =
                parseFloat(row.querySelector(".portal-price").value) || 0;


            let exchangeRate =
                parseFloat(e.target.value) || 0;

            let total = Math.round(portalPrice * exchangeRate);

            let formattedTotal = total.toLocaleString();

            row.querySelector(".total-label").innerText = formattedTotal;

        });
    </script> --}}

    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        function updateRowCalculation(row) {

            let rawValue = row.querySelector(".exchange-input")?.value;
            let sellingRate = rawValue !== "" ? parseFloat(rawValue) : null;

            let portalPrice = parseFloat(row.querySelector(".portal-price")?.value) || 0;
            let exchangeRate = parseFloat(row.querySelector(".base-exchange-rate")?.value) || 0;

            let total = 0;
            let profit = 0;

            // TOTAL
            let totalLabel = row.querySelector(".total-label");
            if (totalLabel) {
                if (sellingRate !== null && sellingRate > 0) {
                    total = sellingRate * portalPrice;
                    totalLabel.textContent = formatNumber(Math.round(total));
                } else {
                    totalLabel.textContent = "-";
                }
            }

            // PROFIT
            let profitLabel = row.querySelector(".profit-label");
            if (profitLabel) {
                if (sellingRate !== null && sellingRate > 0 && exchangeRate > 0) {
                    profit = total - exchangeRate;
                    profitLabel.textContent = formatNumber(Math.round(profit));
                } else {
                    profitLabel.textContent = "-";
                }
            }

            // hidden input
            let profitInput = row.querySelector(".profit-input");
            if (profitInput) {
                profitInput.value = profit > 0 ? profit : '';
            }
        }

        document.addEventListener("input", function(e) {
            if (e.target.classList.contains("exchange-input")) {
                let row = e.target.closest("tr");
                if (row) {
                    updateRowCalculation(row);
                }
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("#invoice-items tr").forEach(function(row) {
                updateRowCalculation(row);
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalSelector = '.modal[id^="manage-price-"]';

            document.querySelectorAll(`${modalSelector} form`).forEach(function(form) {
                form.addEventListener("submit", function() {
                    const modal = this.closest(".modal");
                    if (modal?.id) {
                        sessionStorage.setItem("roamsim_manage_price_modal", modal.id);
                    }
                });
            });

            const reopenModalId = sessionStorage.getItem("roamsim_manage_price_modal");
            if (reopenModalId) {
                sessionStorage.removeItem("roamsim_manage_price_modal");

                const modalElement = document.getElementById(reopenModalId);
                if (modalElement && window.bootstrap) {
                    setTimeout(function() {
                        bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }, 150);
                }
            }
        });
    </script>
@endsection
