@extends('admin.layouts.index')
@section('title', 'Customer Wallet')
@section('content')
    <style>
        .wallet-info-box {
            display: flex;
            align-items: center;
            padding: 20px 22px;
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.025);
            transition: all 0.2s ease;
        }

        .wallet-info-box:hover {
            border-color: rgba(13, 110, 253, 0.35);
            background: rgba(13, 110, 253, 0.04);
        }

        .wallet-balance-box {
            position: relative;
            overflow: hidden;
            border-width: 1px;
        }

        .wallet-balance-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .wallet-balance-box.wallet-state-active {
            border-color: rgba(32, 201, 151, 0.30);
            background: linear-gradient(135deg,
                    rgba(32, 201, 151, 0.10),
                    rgba(32, 201, 151, 0.035));
        }

        .wallet-balance-box.wallet-state-active::before {
            background: #20c997;
        }

        .wallet-balance-box.wallet-state-inactive {
            border-color: rgba(220, 53, 69, 0.30);
            background: linear-gradient(135deg,
                    rgba(220, 53, 69, 0.10),
                    rgba(220, 53, 69, 0.035));
        }

        .wallet-balance-box.wallet-state-inactive::before {
            background: #dc3545;
        }

        .wallet-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            border: 1px solid transparent;
            border-radius: 50rem;
        }

        .wallet-status-badge.status-active {
            color: #16835c;
            border-color: rgba(32, 201, 151, 0.22);
            background: rgba(32, 201, 151, 0.10);
        }

        .wallet-status-badge.status-inactive {
            color: #c93445;
            border-color: rgba(220, 53, 69, 0.22);
            background: rgba(220, 53, 69, 0.10);
        }

        .wallet-status-dot {
            position: relative;
            width: 8px;
            height: 8px;
            flex: 0 0 8px;
            border-radius: 50%;
        }

        .status-active .wallet-status-dot {
            background: #20c997;
            box-shadow: 0 0 0 4px rgba(32, 201, 151, 0.14);
        }

        .status-inactive .wallet-status-dot {
            background: #dc3545;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.14);
        }

        .wallet-status-note {
            display: block;
            margin-top: 5px;
            color: var(--bs-secondary-color);
            font-size: 11px;
            line-height: 1.4;
        }

        .customer-account-warning {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 10px;
            padding: 11px 13px;
            color: #b42318;
            border: 1px solid rgba(220, 53, 69, 0.22);
            border-radius: 8px;
            background: rgba(220, 53, 69, 0.07);
        }

        .customer-account-warning>i {
            margin-top: 2px;
            color: #dc3545;
            font-size: 18px;
        }

        .customer-account-warning strong {
            display: block;
            margin-bottom: 3px;
            color: #b42318;
            font-size: 13px;
            font-weight: 700;
        }

        .customer-account-warning small {
            display: block;
            color: #9f2d25;
            font-size: 12px;
            line-height: 1.5;
        }

        .wallet-status-note-success {
            display: block;
            margin-top: 10px;
            color: #198754;
            font-size: 12px;
            font-weight: 500;
        }

        .wallet-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 22px;
        }

        .wallet-divider {
            width: 1px;
            height: 24px;
            display: inline-block;
            background-color: #adb5bf;
        }

        .customer-icon {
            color: #6ea8fe;
            background: rgba(13, 110, 253, 0.12);
        }

        .balance-icon {
            color: #20c997;
            background: rgba(32, 201, 151, 0.12);
        }

        .wallet-state-inactive .balance-icon {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.12);
        }

        .wallet-label {
            display: block;
            color: var(--bs-secondary-color);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .wallet-currency {
            font-size: 13px;
            font-weight: 600;
            color: var(--bs-secondary-color);
        }

        @media (max-width: 767.98px) {
            .wallet-info-box {
                padding: 17px;
            }

            .wallet-balance-box {
                min-height: 118px;
            }

            .wallet-balance-content {
                width: 100%;
            }

            .wallet-customer-content {
                flex-wrap: wrap;
            }
        }
    </style>

    @php
        $isCustomerActive = (int) ($customer->status ?? 0) === 1;
        $isEmailVerified = !is_null($customer->email_verified_at);
    @endphp
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 customer-page-title">Wallet Setting</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"
                            class="customer-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.index') }}"
                            class="customer-breadcrumb-link">Customers</a></li>
                    <li class="breadcrumb-item active customer-breadcrumb-current">Wallet Setting</li>
                </ol>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('customer.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                        <h5 class="card-title">Wallet Information</h5>

                        <div class="d-flex gap-3 align-items-center">
                            @if ($customer->customerWallet)
                                <label for="wallet-status" class="card-title mb-0">
                                    Wallet Status:
                                </label>
                                <div class="form-check form-switch form-check-secondary fs-xxl mb-0">
                                    <input id="wallet-status" name="status" type="checkbox"
                                        class="form-check-input mt-1 all-btn status-btn"
                                        {{ $isWalletActive ? 'checked' : '' }}
                                        data-url="{{ route('customer.wallet.status', $customer->customerWallet?->id ?? '') }}">
                                </div>
                                <span class="wallet-divider"></span>
                            @endif
                            <x-create-action :url="route('customer.wallet.create', $customer->id)" menu-text="Wallet" icon="ti-plus" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-7 col-md-5">
                                <div class="wallet-info-box h-100">
                                    <div class="d-flex align-items-center">

                                        <div class="wallet-icon customer-icon flex-shrink-0">
                                            <i class="ti ti-user"></i>
                                        </div>

                                        <div
                                            class="wallet-customer-content ms-3 d-flex justify-content-between align-items-center flex-grow-1 gap-3 w-100">
                                            <div class="customer-detail">
                                                <h5 class="mb-1 mt-1 fw-semibold">
                                                    {{ $customer->name }}
                                                </h5>

                                                <p class="mb-0 text-muted small text-break">
                                                    <i class="ti ti-mail me-1"></i>
                                                    {{ $customer->email }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5 col-md-7">
                                <div
                                    class="wallet-info-box wallet-balance-box h-100 {{ $isWalletActive ? 'wallet-state-active' : 'wallet-state-inactive' }}">
                                    <div class="d-flex gap-3 align-items-start w-100">
                                        <div class="wallet-icon balance-icon flex-shrink-0">
                                            <i class="ti ti-wallet"></i>
                                        </div>

                                        <div class="wallet-balance-content flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span class="wallet-label">Remaining Balance</span>

                                                <span
                                                    class="wallet-status-badge {{ $isWalletActive ? 'status-active' : 'status-inactive' }}">
                                                    <span class="wallet-status-dot"></span>
                                                    {{ $isWalletActive ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>

                                            <h3
                                                class="mb-0 mt-2 fw-bold {{ $isWalletActive ? 'text-success' : 'text-danger' }}">
                                                <span
                                                    data-target="{{ $customer->customerWallet ? $customer->customerWallet->balance : 0 }}">0</span>
                                                <small class="wallet-currency">MMK</small>
                                            </h3>

                                            @if (!$isCustomerActive || !$isEmailVerified)
                                                <div class="customer-account-warning">
                                                    <i class="ti ti-alert-triangle"></i>

                                                    <div>
                                                        <strong>Customer Account Warning</strong>

                                                        <small>
                                                            @if (!$isCustomerActive && !$isEmailVerified)
                                                                This customer account is inactive and the email address has
                                                                not been verified.
                                                                Review the account carefully before approving or adding any
                                                                wallet top-up.
                                                            @elseif (!$isCustomerActive)
                                                                This customer account is currently inactive.
                                                                Please verify the account status before approving or adding
                                                                any wallet top-up.
                                                            @else
                                                                This customer's email address has not been verified.
                                                                Please confirm the customer information before approving or
                                                                adding any wallet top-up.
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <small class="wallet-status-note wallet-status-note">
                                                    {{ $isWalletActive ? 'This wallet is ready for top-ups and package purchases.' : 'This wallet is disabled and cannot be used by the customer.' }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div data-table data-table-rows-per-page="5" class="card">
                    <div class="card-header border-light justify-content-between">
                        <div class="d-flex gap-2">
                            <h5 class="card-title">Wallet Top-Up Requests</h5>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">

                            <form id="topup-date-filter-form" action="{{ url()->current() }}" method="GET"
                                class="d-flex align-items-center gap-2">

                                <span class="fw-semibold text-nowrap">
                                    Filter By:
                                </span>

                                <div class="position-relative">
                                    <input type="text" id="topup-filter-date" name="topup_date"
                                        value="{{ request('topup_date') }}" class="form-control" data-provider="flatpickr"
                                        data-date-format="Y-m-d" data-default-date="{{ $request->topup_date ?? '' }}"
                                        placeholder="Select date" autocomplete="off" readonly>
                                </div>
                            </form>

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
                                    <th data-table-sort>No</th>
                                    <th data-table-sort="customer">Balance</th>
                                    <th data-table-sort>Pending Date</th>
                                    <th data-table-sort data-column="status">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                @forelse ($topupRequests ?? [] as $transaction)
                                    @php
                                        $isPending = $transaction->transaction_state === 'pending';
                                    @endphp
                                    <tr>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    <h5 class="fs-base mb-0"><a data-sort="user" href="#"
                                                            class="link-reset">{{ number_format($transaction->amount) . ' MMK' }}</a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="transaction-date">
                                                <strong>
                                                    {{ optional($transaction->created_at)->format('d M Y') }}
                                                </strong>

                                                <small>
                                                    {{ optional($transaction->created_at)->format('h:i A') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge badge-soft-{{ $isPending ? 'warning' : 'success' }} text-{{ $isPending ? 'warning' : 'success' }} badge-label fs-10">{{ ucfirst($transaction->transaction_state) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('customer.wallet.show', ['transaction' => $transaction, 'customer_id' => $customer->id]) }}"
                                                class="request-detail-link text-decoration-underline text-secondary">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                @if (request()->filled('topup_date'))
                                                    No Top Up history found for
                                                    <strong>
                                                        {{ \Carbon\Carbon::parse(request('topup_date'))->format('d M Y') }}
                                                    </strong>.
                                                @else
                                                    No Top Up history found.
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="Wallet"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>
                </div>

                <div data-table data-table-rows-per-page="5" class="card">
                    <div class="card-header border-light justify-content-between">
                        <div class="d-flex gap-2">
                            <h5 class="card-title">Transaction History</h5>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">

                            <form id="transaction-date-filter-form" action="{{ url()->current() }}" method="GET"
                                class="d-flex align-items-center gap-2 flex-wrap">

                                @if (request()->filled('topup_date'))
                                    <input type="hidden" name="topup_date" value="{{ request('topup_date') }}">
                                @endif

                                <span class="fw-semibold text-nowrap">
                                    Filter By:
                                </span>

                                <div>
                                    <input type="text" id="transaction-filter-date" name="transaction_date"
                                        value="{{ request('transaction_date') }}" class="form-control"
                                        data-provider="flatpickr" data-date-format="Y-m-d" placeholder="Select date"
                                        data-default-date="{{ request()->transaction_date }}" autocomplete="off"
                                        readonly>
                                </div>

                                @if (request()->filled('transaction_date'))
                                    <a href="{{ url()->current() }}{{ request()->filled('topup_date') ? '?topup_date=' . urlencode(request('topup_date')) : '' }}"
                                        class="btn btn-light" title="Clear transaction date filter">

                                        <i class="ti ti-x"></i>
                                    </a>
                                @endif
                            </form>

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
                                    <th data-table-sort>No</th>
                                    <th data-table-sort="customer">Transaction</th>
                                    <th data-table-sort="customer">Reference</th>
                                    <th data-table-sort>Date</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Balance After</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    @php
                                        $isCredit = $transaction->type === 'credit';

                                        $transactionTitle = match ($transaction->reference_type) {
                                            'topup' => 'Wallet Top-up',
                                            'package_purchase' => 'Package Purchase',
                                            'return' => 'Wallet Return',
                                            default => ucwords(str_replace('_', ' ', $transaction->reference_type)),
                                        };

                                        $referenceLabel = match ($transaction->reference_type) {
                                            'topup' => 'Top Up',
                                            'package_purchase' => 'Package Purchase',
                                            'return' => 'Return',
                                            default => ucwords(str_replace('_', ' ', $transaction->reference_type)),
                                        };

                                        $transactionState = strtolower($transaction->transaction_state ?? 'pending');

                                        $statusColor = match ($transactionState) {
                                            'pending' => 'warning',
                                            'approved', 'complete', 'completed' => 'success',
                                            'cancelled', 'rejected', 'failed' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp

                                    <tr>
                                        <td>
                                            <h5 class="m-0">
                                                <span class="link-reset">
                                                    {{ $loop->iteration }}
                                                </span>
                                            </h5>
                                        </td>

                                        <td>
                                            <div class="d-flex flex-column justify-content-start align-items-start gap-1">

                                                <strong>
                                                    {{ $transactionTitle }}
                                                </strong>

                                                <small class="badge badge-soft-secondary">
                                                    {{ $isCredit ? 'Balance credited' : ($transaction->reference_type === 'return' ? 'Balance returned' : 'Wallet payment') }}
                                                </small>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h5 class="fs-base mb-0">
                                                    <span class="link-reset">
                                                        {{ $referenceLabel }}
                                                    </span>
                                                </h5>

                                                @if ($transaction->reference_type === 'topup')
                                                    <span
                                                        class="badge badge-soft-{{ $statusColor }} text-{{ $statusColor }}">

                                                        {{ ucfirst($transactionState) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <strong>
                                                    {{ optional($transaction->created_at)->format('d M Y') }}
                                                </strong>

                                                <small>
                                                    {{ optional($transaction->created_at)->format('h:i A') }}
                                                </small>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="{{ $isCredit ? 'text-success' : 'text-danger' }}">
                                                {{ $isCredit ? '+' : '-' }}
                                                {{ number_format($transaction->amount) }} MMK
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            @if ($transactionState === 'pending')
                                                -
                                            @else
                                                {{ number_format($transaction->balance_after) }} MMK
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                @if (request()->filled('transaction_date'))
                                                    No transactions found for

                                                    <strong>
                                                        {{ \Carbon\Carbon::parse(request('transaction_date'))->format('d M Y') }}
                                                    </strong>.
                                                @else
                                                    No transaction history found.
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="Wallet"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
