@extends('admin.layouts.index')
@section('title', 'Customer Wallet')
@section('content')
    @include('components.alert')
    <style>
        .topup-card {
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 2px;
        }

        .topup-card .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }

        .customer-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 22px;
            color: #6ea8fe;
            background: rgba(13, 110, 253, 0.12);
        }

        .customer-summary,
        .topup-box {
            padding: 24px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 10px;
        }

        .customer-summary {
            background: rgba(148, 163, 184, 0.025);
        }

        .info-label {
            display: block;
            color: var(--bs-secondary-color);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .remaining-balance {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px;
            border: 1px solid rgba(13, 110, 253, 0.2);
            border-radius: 10px;
            background: rgba(13, 110, 253, 0.06);
        }

        .remaining-balance h3 small {
            font-size: 12px;
            font-weight: 600;
            color: var(--bs-secondary-color);
        }

        .balance-icon,
        .topup-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.12);
        }

        .balance-icon {
            width: 44px;
            height: 44px;
            font-size: 21px;
        }

        .topup-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 14px;
            font-size: 19px;
        }

        .topup-input-group .input-group-text {
            min-width: 65px;
            justify-content: center;
            font-weight: 600;
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.07);
        }

        .topup-input-group .form-control {
            height: 50px;
            font-size: 18px;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {

            .customer-summary,
            .topup-box {
                padding: 19px;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 customer-page-title">Wallet Setting</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"
                            class="customer-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.index', $customer->id) }}"
                            class="customer-breadcrumb-link">Customers</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.wallet.index', $customer->id) }}"
                            class="customer-breadcrumb-link">Hnin
                            Shwe Sin - Wallet Setting</a></li>
                    <li class="breadcrumb-item customer-breadcrumb-current active">Create</li>
                </ol>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('customer.wallet.index', $customer->id) }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="row justify-content-center">

            <div class="card topup-card">
                <div class="card-header">
                    <div>
                        <h4 class="card-title mb-1">Top Up Wallet</h4>
                        <p class="text-muted mb-0">
                            Add balance to this customer wallet.
                        </p>
                    </div>
                </div>

                <form action="{{ route('customer.wallet.store', ['customer_id' => $customer->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <div class="card-body">
                        <div class="row g-4">

                            <div class="col-lg-5">
                                <div class="customer-summary h-100">

                                    <div class="d-flex align-items-center mb-4">
                                        <div class="wallet-icon customer-icon flex-shrink-0">
                                            <i class="ti ti-user"></i>
                                        </div>

                                        <div class="ms-3">
                                            <h5 class="mb-1 mt-1 fw-semibold">
                                                {{ $customer->name }}
                                            </h5>

                                            <p class="text-muted mb-0 small">
                                                <i class="ti ti-mail me-1"></i>
                                                {{ $customer->email }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="remaining-balance">
                                        <div>
                                            <span class="info-label">
                                                Remaining Balance
                                            </span>

                                            <h3 class="mb-0 mt-1 fw-bold text-secondary">
                                                <span
                                                    data-target="{{ $customer->customerWallet ? $customer->customerWallet->balance : 0 }}">0</span>
                                                <small>MMK</small>
                                            </h3>
                                        </div>

                                        <div class="balance-icon">
                                            <i class="ti ti-wallet"></i>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Top Up Box --}}
                            <div class="col-lg-7">
                                <div class="d-flex flex-column gap-3">
                                    <div class="topup-box">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Enter Top Up Amount</h5>

                                            <p class="text-muted mb-0">
                                                Enter the amount to add to the customer's wallet.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <label for="topupAmount" class="form-label fw-semibold">

                                                Top Up Balance
                                                <span class="text-danger">*</span>
                                            </label>

                                            <div class="input-group topup-input-group">
                                                <span class="input-group-text">
                                                    MMK
                                                </span>

                                                <input type="number" id="topupAmount" name="balance" class="form-control"
                                                    value="{{ old('balance') }}" min="1000" step="1"
                                                    placeholder="Enter amount">
                                            </div>

                                            @error('balance')
                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('customer.wallet.index', $customer->id) }}"
                                                class="btn btn-light px-4">
                                                Cancel
                                            </a>

                                            <button type="submit" name="transaction_action" value="topup"
                                                class="btn btn-primary px-4">

                                                <i class="ti ti-wallet me-1"></i>
                                                Top Up
                                            </button>
                                        </div>
                                    </div>

                                    <div class="topup-box">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Enter Return Amount</h5>

                                            <p class="text-muted mb-0">
                                                Enter the amount to return from the customer's wallet.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <label for="returnAmount" class="form-label fw-semibold">

                                                Return Balance
                                                <span class="text-danger">*</span>
                                            </label>

                                            <div class="input-group topup-input-group">
                                                <span class="input-group-text">
                                                    MMK
                                                </span>

                                                <input type="number" id="returnAmount" name="return_balance"
                                                    class="form-control" value="{{ old('return_balance') }}" min="1"
                                                    step="1" placeholder="Enter amount">
                                            </div>

                                            @error('return_balance')
                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('customer.wallet.index', $customer->id) }}"
                                                class="btn btn-light px-4">
                                                Cancel
                                            </a>

                                            <button type="submit" name="transaction_action" value="return"
                                                class="btn btn-primary px-4">

                                                <i class="ti ti-arrow-back-up me-1"></i>
                                                Return
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>
@endsection
