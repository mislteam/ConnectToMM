@extends('frontend.layouts.index')

@section('title', 'My Wallet')

@section('styles')
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    @include('components.alert')

    @php
        $customer = $customer ?? auth()->user();
        $wallet = $customer?->customerWallet;
        $isWalletActive = (int) ($wallet?->status ?? 0) === 1;

        $customerName = $customer?->name ?? 'Customer';
        $customerEmail = $customer?->email ?? 'Not provided';
        $customerPhone = $customer?->phone ?? 'Not provided';

        $customerInitials = collect(preg_split('/\s+/', trim($customerName)))
            ->filter()
            ->take(2)
            ->map(fn($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        $transactions = $transactions ?? collect();
    @endphp

    <section class="wallet-section py-5" style="margin-top: 150px;">
        <div class="container">

            {{-- Main Page Heading --}}
            <div class="wallet-page-heading">
                <span class="wallet-page-subtitle">Customer Wallet</span>

                <h2>Manage Your Wallet</h2>

                <p>
                    View your wallet balance, submit a top-up request,
                    and review your recent wallet transactions.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-12 mb-4">
                    <div class="wallet-overview-card">
                        <div class="row g-0 align-items-stretch">

                            <div class="col-xl-5 col-lg-5">
                                <div class="wallet-balance-box">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="balance-label">
                                                Available Balance
                                            </span>

                                            <h2 class="balance-amount">
                                                <span data-counter-target="{{ $wallet?->balance ?? 0 }}">0</span>
                                                <small>MMK</small>
                                            </h2>
                                        </div>

                                        <div class="wallet-main-icon">
                                            <i class="fa-solid fa-wallet"></i>
                                        </div>
                                    </div>

                                    <div class="wallet-active-status {{ $isWalletActive ? 'is-active' : 'is-inactive' }}">
                                        <span></span>

                                        {{ $isWalletActive ? 'Active Wallet' : 'Inactive Wallet' }}
                                    </div>

                                    <div class="wallet-circle circle-one"></div>
                                    <div class="wallet-circle circle-two"></div>
                                </div>
                            </div>

                            <div class="col-xl-7 col-lg-7">
                                <div class="customer-information-panel">

                                    <div class="customer-panel-heading">
                                        <div class="customer-profile">
                                            <div class="customer-avatar overflow-hidden">
                                                <img src="{{ auth()->user()->profile_image
                                                    ? asset('storage/profile_images/' . auth()->user()->profile_image)
                                                    : asset('assets/images/user-3.jpg') }}"
                                                    alt="avatar-2" class="img-fluid">
                                            </div>

                                            <div>
                                                <span class="customer-account-label">
                                                    Customer Account
                                                </span>

                                                <h4 class="mb-1">
                                                    {{ $customerName }}
                                                </h4>

                                                <p class="mb-0">
                                                    Personal information connected to your wallet.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="verified-customer">
                                            <i class="fa-solid fa-circle-check"></i>
                                            Verified
                                        </div>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-md-12">
                                            <div class="customer-info-item">
                                                <div class="customer-info-icon">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </div>

                                                <div class="customer-info-content">
                                                    <span>Email Address</span>
                                                    <h6 style="text-transform: none;">{{ $customerEmail }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="topup-request-card {{ $isWalletActive ? '' : 'topup-request-disabled' }}">
                        <div class="row align-items-start g-4">

                            <div class="col-xl-5 col-lg-5">
                                <div class="topup-card-heading">
                                    <div class="topup-heading-icon {{ $isWalletActive ? '' : 'is-locked' }}">
                                        <i class="fa-solid {{ $isWalletActive ? 'fa-coins' : 'fa-lock' }}"></i>
                                    </div>

                                    <div>
                                        <span class="topup-small-title">
                                            Wallet Top-up
                                        </span>

                                        <h4>{{ $isWalletActive ? 'Enter Top-up Amount' : 'Top-up Is Unavailable' }}</h4>
                                        <p>
                                            @if ($isWalletActive)
                                                Enter the amount you want to add to your wallet.
                                                Payment details will be provided on the next page.
                                            @else
                                                Your wallet is currently inactive. You cannot submit a top-up request
                                                until your wallet has been activated.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-7 col-lg-7">
                                @unless ($isWalletActive)
                                    <div class="wallet-inactive-notice" role="alert">
                                        <div class="wallet-inactive-notice-icon">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                        </div>
                                        <div>
                                            <strong>Wallet activation required</strong>
                                            <span>Please contact support or an administrator to activate your wallet.</span>
                                        </div>
                                    </div>
                                @endunless

                                <form action="{{ route('frontend.user.topup') }}" method="POST"
                                    class="{{ $isWalletActive ? '' : 'topup-form-disabled' }}">
                                    @csrf
                                    <div class="row g-3">

                                        <div class="col-md-12">
                                            <label for="topupAmount" class="form-label">
                                                Top-up Amount
                                                @if ($isWalletActive)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            <div class="input-group wallet-form-group">
                                                <span class="input-group-text">
                                                    <i class="fa-solid {{ $isWalletActive ? 'fa-coins' : 'fa-lock' }}"></i>
                                                </span>

                                                <input type="number" id="topupAmount" name="amount" class="form-control"
                                                    min="1000" step="1000"
                                                    placeholder="{{ $isWalletActive ? 'Enter top-up amount' : 'Wallet is inactive' }}"
                                                    @if ($isWalletActive) required @else disabled aria-disabled="true" @endif>

                                                <span class="input-group-text amount-currency">
                                                    MMK
                                                </span>
                                            </div>

                                            <small class="form-helper {{ $isWalletActive ? '' : 'text-danger' }}">
                                                {{ $isWalletActive
                                                    ? 'Minimum top-up amount is 1,000 MMK.'
                                                    : 'Top-up is disabled while your wallet is inactive.' }}
                                            </small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label d-none d-md-block">
                                                &nbsp;
                                            </label>

                                            <button type="submit" class="btn wallet-submit-button w-100"
                                                @disabled(!$isWalletActive)>
                                                @if ($isWalletActive)
                                                    Continue
                                                    <i class="fa-solid fa-arrow-right ms-2"></i>
                                                @else
                                                    Wallet Inactive
                                                    <i class="fa-solid fa-lock ms-2"></i>
                                                @endif
                                            </button>
                                        </div>

                                        <div class="col-12">
                                            <div
                                                class="wallet-security {{ $isWalletActive ? '' : 'wallet-security-disabled' }}">
                                                <i
                                                    class="fa-solid {{ $isWalletActive ? 'fa-shield-halved' : 'fa-lock' }}"></i>

                                                <span>
                                                    {{ $isWalletActive
                                                        ? 'Your request will be securely reviewed before the balance is added to your wallet.'
                                                        : 'The top-up form will become available after your wallet is activated.' }}
                                                </span>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="transaction-provider-card">
                        <div class="transaction-provider-head">
                            <h5 class="transaction-provider-title provider-roam">
                                <span>Transaction History</span>
                            </h5>

                            <form id="transaction-date-filter-form" action="{{ url()->current() }}" method="GET"
                                class="transaction-date-filter-form">
                                <span class="transaction-filter-label">Filter By:</span>

                                <input type="text" id="transaction-filter-date" name="transaction_date"
                                    value="{{ request('transaction_date') }}" class="form-control transaction-filter-input"
                                    data-provider="flatpickr" data-date-format="Y-m-d" placeholder="Select date"
                                    autocomplete="off">

                                @if (request()->filled('transaction_date'))
                                    <a href="{{ url()->current() }}" class="transaction-filter-clear"
                                        title="Clear transaction date filter" aria-label="Clear transaction date filter">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif
                            </form>

                        </div>
                        <div class="table-responsive">
                            <table class="table transaction-history-table">
                                <thead>
                                    <tr>
                                        <th>Transaction Type</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th class="text-center">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody data-transaction-table="roam-only">
                                    @forelse ($transactions as $transaction)
                                        @php
                                            $isCredit = $transaction->type === 'credit';

                                            $transactionTitle = match ($transaction->reference_type) {
                                                'topup' => 'Wallet Top-up',
                                                'package_purchase' => 'Package Purchase',
                                                'return' => 'Wallet Return',
                                                default => ucwords(
                                                    str_replace('_', ' ', $transaction->reference_type),
                                                ),
                                            };

                                            $referenceLabel = match ($transaction->reference_type) {
                                                'topup' => 'Top Up',
                                                'package_purchase' => 'Package Purchase',
                                                'return' => 'Return',
                                                default => ucwords(
                                                    str_replace('_', ' ', $transaction->reference_type),
                                                ),
                                            };

                                            $transactionState = strtolower(
                                                $transaction->transaction_state ?? 'pending',
                                            );
                                            $isApproved = $transactionState === 'approved';
                                        @endphp
                                        <tr>
                                            <td data-label="transaction ID">
                                                <div class="d-flex flex-column gap-1">
                                                    <strong>{{ $transactionTitle }}</strong>
                                                    <small>
                                                        {{ $isCredit ? 'Balance credited' : ($transaction->reference_type === 'return' ? 'Balance returned' : 'Wallet payment') }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td data-label="Product Name">
                                                <strong>{{ $referenceLabel }}</strong>
                                            </td>
                                            <td data-label="Product Name">
                                                <div class="transaction-date">
                                                    <strong>
                                                        {{ optional($transaction->created_at)->format('d M Y') }}
                                                    </strong>

                                                    <span>
                                                        {{ optional($transaction->created_at)->format('h:i A') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td data-label="Status" class="transaction-status text-warning">
                                                @if ($transaction->reference_type === 'topup')
                                                    <span
                                                        class="transaction-reference-status status-{{ $transactionState }}">
                                                        {{ $isApproved ? 'Payment Approved' : 'Pending Payment' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="transaction-amount {{ $isCredit ? 'amount-credit' : 'amount-debit' }}">
                                                    {{ $isCredit ? '+' : '-' }}
                                                    {{ number_format($transaction->amount) }} MMK
                                                </span>
                                            </td>

                                            <td data-label="Actions">
                                                <div class="transaction-action-group">
                                                    <a href="{{ route('frontend.user.topup-detail', $transaction->id) }}"
                                                        class="transaction-detail-link">Detail</a>
                                                    @if ($transactionState == 'pending')
                                                        <a href="{{ route('frontend.user.topup-payment', $transaction->id) }}"
                                                            class="btn btn-primary btn-sm transaction-pay-btn">Pay</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="transaction-empty-row">
                                            <td colspan="6">
                                                <div class="transaction-empty-state">
                                                    @if (request()->filled('transaction_date'))
                                                        <h6>No transactions found</h6>
                                                        <p>No wallet transactions were found for
                                                            {{ \Carbon\Carbon::parse(request('transaction_date'))->format('d M Y') }}.
                                                        </p>
                                                    @else
                                                        <h6>No Transactions yet</h6>
                                                        <p>Your transaction history will appear here after you top up your
                                                            wallet or purchase a package.</p>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($transactions && $transactions->count() > 0)
                            <div class="transaction-pagination-wrap">
                                <div>
                                    Showing {{ $transactions->firstItem() ?? 0 }} to
                                    {{ $transactions->lastItem() ?? 0 }} of
                                    {{ $transactions->total() }} wallet transactions
                                </div>

                                <div>
                                    {{ $transactions->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    <style>
        .wallet-section {
            background:
                radial-gradient(circle at top left,
                    rgba(8, 53, 134, 0.05),
                    transparent 32%),
                #ffffff;
        }

        .wallet-page-heading {
            max-width: 720px;
            margin: 0 auto 38px;
            text-align: center;
        }

        .wallet-page-subtitle {
            display: inline-flex;
            align-items: center;
            margin-bottom: 8px;
            color: #0a8e96;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .wallet-page-heading h2 {
            margin-bottom: 12px;
            color: #202a3b;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 700;
            letter-spacing: -0.8px;
        }

        .wallet-page-heading p {
            max-width: 650px;
            margin: 0 auto;
            color: #788395;
            font-size: 15px;
            line-height: 1.7;
        }


        .wallet-overview-card,
        .topup-request-card,
        .transaction-history-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e7ebf1;
            border-radius: 20px;
            box-shadow: 0 12px 35px rgba(24, 39, 75, 0.07);
        }

        .wallet-balance-box {
            position: relative;
            min-height: 220px;
            margin: 20px;
            padding: 28px;
            overflow: hidden;
            color: #ffffff;
            border-radius: 18px;
            background: linear-gradient(135deg,
                    #083586 0%,
                    #1367b9 55%,
                    #08a0a5 100%);
        }

        .balance-label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
        }

        .balance-amount {
            position: relative;
            z-index: 2;
            margin: 0;
            color: #ffffff;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: -1px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .balance-amount small {
            margin-left: 5px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .wallet-main-icon {
            position: relative;
            z-index: 2;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 25px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .wallet-active-status {
            position: absolute;
            bottom: 28px;
            left: 28px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.14);
        }

        .wallet-active-status span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .wallet-active-status.is-active span {
            background: #6cff9a;
            box-shadow: 0 0 0 4px rgba(108, 255, 154, 0.15);
        }

        .wallet-active-status.is-inactive span {
            background: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .wallet-circle {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .circle-one {
            right: -70px;
            bottom: -70px;
            width: 190px;
            height: 190px;
        }

        .circle-two {
            right: 75px;
            bottom: -75px;
            width: 120px;
            height: 120px;
        }

        .customer-information-panel {
            height: 100%;
            padding: 30px;
            border-left: 1px solid #edf0f4;
        }

        .customer-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
            padding-bottom: 22px;
            border-bottom: 1px solid #edf0f4;
        }

        .customer-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .customer-profile h4 {
            color: #252f40;
            font-size: 21px;
            font-weight: 700;
        }

        .customer-profile p {
            color: #8993a4;
            font-size: 13px;
            line-height: 1.5;
        }

        .customer-account-label {
            display: block;
            margin-bottom: 3px;
            color: #0a8e96;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .customer-avatar {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #ffffff;
            font-size: 19px;
            font-weight: 700;
            border-radius: 16px;
            background: linear-gradient(135deg, #083586, #0ba0a5);
        }

        .verified-customer {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            color: #16835c;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid rgba(22, 131, 92, 0.16);
            border-radius: 30px;
            background: rgba(22, 131, 92, 0.07);
        }

        .customer-info-item {
            min-height: 75px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            border: 1px solid #edf0f4;
            border-radius: 13px;
            background: #fafbfd;
            transition: 0.2s ease;
        }

        .customer-info-item:hover {
            border-color: #dbe3ed;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(24, 39, 75, 0.05);
        }

        .customer-info-icon {
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #083586;
            font-size: 17px;
            border-radius: 11px;
            background: rgba(8, 53, 134, 0.08);
        }

        .customer-info-content {
            min-width: 0;
        }

        .customer-info-item span {
            display: block;
            margin-bottom: 3px;
            color: #929bab;
            font-size: 11px;
        }

        .customer-info-item h6 {
            margin: 0;
            overflow-wrap: anywhere;
            color: #303a4a;
            font-size: 14px;
            font-weight: 600;
        }

        .topup-request-card {
            padding: 30px;
        }

        .topup-card-heading {
            display: flex;
            align-items: flex-start;
            gap: 17px;
        }

        .topup-heading-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 48px;
            color: #083586;
            font-size: 18px;
            border-radius: 14px;
            background: rgba(8, 53, 134, 0.08);
        }

        .topup-heading-icon.is-locked {
            color: #c24141;
            background: rgba(220, 53, 69, 0.1);
        }

        .topup-request-card.topup-request-disabled {
            border-color: rgba(220, 53, 69, 0.22);
            background: linear-gradient(135deg, #ffffff 0%, #fff9f9 100%);
        }

        .wallet-inactive-notice {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            padding: 14px 16px;
            color: #8f2f35;
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 12px;
            background: rgba(220, 53, 69, 0.07);
        }

        .wallet-inactive-notice-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 36px;
            border-radius: 10px;
            background: rgba(220, 53, 69, 0.12);
        }

        .wallet-inactive-notice strong,
        .wallet-inactive-notice span {
            display: block;
        }

        .wallet-inactive-notice strong {
            margin-bottom: 3px;
            font-size: 13px;
        }

        .wallet-inactive-notice span {
            color: #a45d62;
            font-size: 12px;
            line-height: 1.5;
        }

        .topup-form-disabled .wallet-form-group {
            opacity: 0.72;
        }

        .topup-form-disabled .wallet-form-group .form-control,
        .topup-form-disabled .wallet-form-group .input-group-text {
            color: #9098a6;
            border-color: #e4e7ec;
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .topup-small-title,
        .transaction-small-title {
            display: block;
            margin-bottom: 4px;
            color: #0a8e96;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .topup-card-heading h4 {
            margin-bottom: 6px;
            color: #252f40;
            font-size: 22px;
            font-weight: 700;
        }

        .topup-card-heading p {
            max-width: 460px;
            margin: 0;
            color: #818b9b;
            font-size: 13px;
            line-height: 1.6;
        }

        .topup-request-card .form-label {
            display: block;
            margin-bottom: 8px;
            color: #354052;
            font-size: 13px;
            font-weight: 600;
        }

        .wallet-form-group .input-group-text {
            min-width: 52px;
            justify-content: center;
            color: #748095;
            border-color: #dfe5ec;
            background: #f8fafc;
        }

        .wallet-form-group .form-control {
            min-height: 54px;
            color: #344054;
            font-size: 15px;
            font-weight: 600;
            border-color: #dfe5ec;
        }

        .wallet-form-group .form-control:focus {
            border-color: #2472ba;
            box-shadow: 0 0 0 3px rgba(36, 114, 186, 0.1);
        }

        .amount-currency {
            min-width: auto !important;
            padding-right: 18px;
            padding-left: 18px;
            color: #5c6879 !important;
            font-size: 12px;
            font-weight: 700;
        }

        .form-helper {
            display: block;
            margin-top: 6px;
            color: #929bab;
            font-size: 11px;
        }

        .wallet-submit-button {
            min-height: 54px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            border: 0;
            border-radius: 11px;
            background: linear-gradient(135deg, #083586, #087f9f);
            box-shadow: 0 10px 24px rgba(8, 53, 134, 0.18);
            transition: 0.25s ease;
        }

        .wallet-submit-button:hover,
        .wallet-submit-button:focus {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 13px 28px rgba(8, 53, 134, 0.26);
        }

        .wallet-submit-button:disabled,
        .wallet-submit-button.disabled {
            color: #8b919c;
            background: #e4e7ec;
            box-shadow: none;
            cursor: not-allowed;
            opacity: 1;
            transform: none;
        }

        .wallet-submit-button:disabled:hover,
        .wallet-submit-button:disabled:focus {
            color: #8b919c;
            transform: none;
            box-shadow: none;
        }

        .wallet-security {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7b8696;
            font-size: 11px;
        }

        .wallet-security i {
            color: #1b8a61;
        }

        .wallet-security.wallet-security-disabled {
            color: #a45d62;
        }

        .wallet-security.wallet-security-disabled i {
            color: #dc3545;
        }

        table th {
            text-transform: uppercase;
        }

        .transaction-history-card {
            padding: 0;
        }

        .transaction-amount {
            font-weight: 700;
            white-space: nowrap;
        }

        .amount-credit {
            color: #23a879;
        }

        .amount-debit {
            color: #ad4500;
        }

        .transaction-empty-state {
            padding: 42px 20px;
            text-align: center;
        }

        .transaction-empty-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 13px;
            color: #083586;
            font-size: 23px;
            border-radius: 16px;
            background: rgba(8, 53, 134, 0.08);
        }

        .transaction-empty-state h5 {
            margin-bottom: 6px;
            color: #303a4a;
            font-size: 16px;
            font-weight: 700;
        }

        .transaction-empty-state p {
            margin: 0;
            color: #929bab;
            font-size: 13px;
        }

        .transaction-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 16px 30px;
            color: #929bab;
            font-size: 11px;
            border-top: 1px solid #edf0f4;
            background: #fafbfd;
        }

        .view-all-transactions {
            color: #083586;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .view-all-transactions:hover {
            color: #ad4500;
        }

        .transaction-reference-box {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .transaction-reference-title {
            color: #303a4a;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
        }

        .transaction-reference-status.status-pending {
            color: #ff9c1a;
        }

        .transaction-reference-status.status-complete,
        .transaction-reference-status.status-completed,
        .transaction-reference-status.status-approved {
            color: #16835c;
        }

        .transaction-reference-status.status-rejected,
        .transaction-reference-status.status-cancelled,
        .transaction-reference-status.status-canceled {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        @media (max-width: 991.98px) {
            .customer-information-panel {
                border-top: 1px solid #edf0f4;
                border-left: 0;
            }

            .topup-card-heading {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 767.98px) {
            .wallet-section {
                padding-top: 30px !important;
            }

            .wallet-page-heading {
                margin-bottom: 28px;
                text-align: left;
            }

            .wallet-page-heading p {
                margin: 0;
            }

            .wallet-balance-box {
                min-height: 200px;
                margin: 14px;
                padding: 22px;
            }

            .balance-amount {
                font-size: 29px;
            }

            .wallet-active-status {
                bottom: 22px;
                left: 22px;
            }

            .customer-information-panel,
            .topup-request-card {
                padding: 22px;
            }

            .customer-panel-heading {
                align-items: flex-start;
            }

            .verified-customer {
                display: none;
            }

            .transaction-card-heading {
                padding: 22px;
            }

            .transaction-card-footer {
                align-items: flex-start;
                flex-direction: column;
                padding: 15px 22px;
            }
        }

        .topup-detail-btn {
            font-weight: 700;
            text-decoration: underline;
            font-size: 16px;
        }

        @media (max-width: 575.98px) {
            .customer-panel-heading {
                flex-direction: column;
            }

            .topup-card-heading {
                align-items: flex-start;
            }

            .transaction-heading-icon {
                display: none;
            }
        }


        .transaction-detail-link {
            color: #0d6efd;
            text-decoration: underline;
            font-weight: 600;
        }

        .transaction-detail-link:hover,
        .transaction-detail-link:focus {
            color: #084298;
        }

        .transaction-history-shell {
            max-width: 1080px;
            margin: 0 auto;
        }

        .transaction-history-title {
            color: #1d2a7a;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 22px;
        }

        .transaction-history-panel {
            display: none;
        }

        .transaction-history-panel.is-active {
            display: block;
        }

        .transaction-provider-card {
            background: #fff;
            border: 1px solid #ececf4;
            border-radius: 0;
            box-shadow: none;
            padding: 18px 18px 12px;
            margin-bottom: 22px;
        }

        .transaction-provider-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 0;
            padding: 0 0 16px;
        }

        .transaction-date-filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .transaction-filter-label {
            color: #313749;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .transaction-filter-input {
            width: 280px;
            height: 40px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #313749;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 12px;
            pointer-events: auto;
        }

        .transaction-filter-input::placeholder {
            color: #9aa6c1;
        }

        .transaction-filter-input:focus {
            border-color: #dee2e6;
            box-shadow: none;
        }

        .transaction-filter-clear {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d8dfef;
            border-radius: 4px;
            color: #6c768f;
            text-decoration: none;
            background: #fff;
        }

        .transaction-filter-clear:hover,
        .transaction-filter-clear:focus {
            color: #173f93;
            border-color: #173f93;
        }

        .transaction-provider-title {
            display: flex;
            align-items: center;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #313749;
        }

        .transaction-provider-title::before {
            display: none;
        }

        .provider-roam,
        .provider-joytel {
            color: #313749;
        }

        .transaction-history-table {
            margin-bottom: 0;
        }

        .transaction-history-table thead th {
            border-bottom: 1px solid #ececf4;
            color: #151515;
            font-size: 12px;
            font-weight: 700;
            padding: 14px 10px;
            white-space: nowrap;
        }

        .transaction-history-table tbody td {
            border-color: #f0f1f6;
            color: #313749;
            font-size: 14px;
            padding: 16px 10px;
            vertical-align: middle;
        }

        .transaction-history-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .transaction-muted-time {
            color: #9aa2b5;
            font-size: 12px;
            font-weight: 500;
        }

        .transaction-status {
            font-weight: 700;
        }

        .transaction-status.text-warning {
            color: #ff9c1a !important;
        }

        .transaction-status.text-success {
            color: #1ea85d !important;
        }

        .transaction-status.text-info {
            color: #1f9ec9 !important;
        }

        .transaction-status.text-danger {
            color: #e45757 !important;
        }

        .transaction-action-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .transaction-pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 18px 0 6px;
            color: #6c768f;
            font-size: 14px;
        }

        .transaction-pagination-wrap .pagination {
            margin-bottom: 0;
        }

        .transaction-pagination-wrap nav {
            display: block !important;
        }

        .transaction-pagination-wrap nav .small.text-muted {
            display: none;
        }

        .transaction-pagination-wrap nav .d-none.flex-sm-fill.d-sm-flex {
            display: block !important;
        }

        .transaction-pagination-wrap nav .d-none.flex-sm-fill.d-sm-flex>div:last-child {
            display: flex;
            justify-content: flex-end;
        }

        .transaction-pagination-wrap nav .page-item+.page-item .page-link {
            margin-left: 0;
        }

        .transaction-pagination-wrap .page-link {
            color: #173f93;
            min-width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            border-color: #d8dfef;
            background: #fff;
        }

        .transaction-pagination-wrap .page-item.active .page-link {
            background-color: #173f93;
            border-color: #173f93;
            color: #fff;
            box-shadow: 0 6px 16px rgba(23, 63, 147, 0.15);
        }

        .transaction-pagination-wrap .page-item.disabled .page-link {
            color: #9aa6c1;
            background: #f7f9fd;
            border-color: #d8dfef;
        }

        .transaction-pay-btn {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .provider-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 4px 4px;
            color: #8690a8;
            font-size: 14px;
        }

        .provider-footer strong {
            color: inherit;
            font-weight: 700;
        }

        .provider-footer-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            text-decoration: none;
        }

        .provider-footer-link.roam-link {
            color: #1ea85d;
        }

        .provider-footer-link.joytel-link {
            color: #8a35ff;
        }

        .transaction-empty-row td {
            padding: 42px 16px;
        }

        .transaction-empty-state {
            max-width: 360px;
            margin: 0 auto;
            text-align: center;
        }

        .transaction-empty-state h6 {
            color: #1d2a7a;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .transaction-empty-state p {
            color: #8a92a6;
            font-size: 14px;
            margin-bottom: 0;
        }

        .transaction-row-hidden {
            display: none;
        }

        @media (max-width: 991.98px) {

            .transaction-history-shell {
                max-width: 100%;
            }

            .transaction-provider-card {
                padding: 16px 14px 10px;
            }

            .transaction-history-table thead th {
                font-size: 11px;
                padding: 12px 8px;
            }

            .transaction-history-table tbody td {
                font-size: 13px;
                padding: 14px 8px;
            }
        }

        @media (max-width: 767.98px) {
            .transaction-history-title {
                font-size: 1.6rem;
            }

            .transaction-provider-head,
            .provider-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .transaction-date-filter-form {
                width: 100%;
                flex-wrap: wrap;
            }

            .transaction-filter-input {
                width: min(100%, 280px);
            }

            .transaction-provider-title {
                flex-wrap: wrap;
            }

            .transaction-provider-card {
                padding: 14px 12px 10px;
                border-radius: 0;
            }

            .transaction-pagination-wrap {
                flex-direction: column;
                align-items: flex-start;
            }

            .transaction-pagination-wrap nav .d-flex.justify-content-between.flex-fill.d-sm-none {
                display: block !important;
            }

            .transaction-pagination-wrap nav .d-flex.justify-content-between.flex-fill.d-sm-none .pagination {
                width: 100%;
                justify-content: space-between;
            }

            .transaction-pagination-wrap .page-link {
                min-width: 44px;
                height: 44px;
            }

            .transaction-history-table,
            .transaction-history-table tbody,
            .transaction-history-table tr,
            .transaction-history-table td {
                display: block;
                width: 100%;
            }

            .transaction-history-table thead {
                display: none;
            }

            .transaction-history-table tbody tr {
                border: 1px solid #edf0f7;
                border-radius: 14px;
                padding: 10px 12px;
                margin-bottom: 12px;
                box-shadow: 0 10px 22px rgba(17, 24, 39, 0.04);
            }

            .transaction-history-table tbody tr:last-child {
                margin-bottom: 0;
            }

            .transaction-history-table tbody td {
                border: 0;
                padding: 8px 0;
                text-align: left !important;
            }

            .transaction-history-table tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                color: #8a92a6;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
            }

            .transaction-history-table tbody td[colspan]::before {
                display: none;
            }

            .transaction-action-group {
                justify-content: flex-start;
            }

            .provider-footer {
                padding-top: 4px;
            }

            .transaction-empty-row td {
                padding: 26px 8px;
            }
        }
    </style>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('[data-counter-target]');
            const transactionFilterDate = document.getElementById('transaction-filter-date');
            const transactionFilterForm = document.getElementById('transaction-date-filter-form');

            const observer = new IntersectionObserver(
                function(entries, currentObserver) {
                    entries.forEach(function(entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        const element = entry.target;
                        const rawTarget = element
                            .getAttribute('data-counter-target')
                            .replace(/,/g, '');

                        const target = Number.parseFloat(rawTarget);

                        if (!Number.isFinite(target) || target <= 0) {
                            element.textContent = formatCounterNumber(0);
                            currentObserver.unobserve(element);
                            return;
                        }

                        let currentValue = 0;
                        const increment = target / 25;

                        function updateCounter() {
                            currentValue = Math.min(currentValue + increment, target);
                            element.textContent = formatCounterNumber(currentValue);

                            if (currentValue < target) {
                                requestAnimationFrame(updateCounter);
                            } else {
                                element.textContent = formatCounterNumber(target);
                            }
                        }

                        updateCounter();
                        currentObserver.unobserve(element);
                    });
                }, {
                    threshold: 0.5,
                }
            );

            counters.forEach(function(counter) {
                observer.observe(counter);
            });

            function initTransactionDateFilter() {
                if (!transactionFilterDate || !transactionFilterForm || !window.flatpickr) {
                    return false;
                }

                if (transactionFilterDate._flatpickr) {
                    return true;
                }

                let lastValue = transactionFilterDate.value;
                let submitting = false;

                function submitTransactionFilter() {
                    if (submitting || transactionFilterDate.value === lastValue) {
                        return;
                    }

                    submitting = true;
                    lastValue = transactionFilterDate.value;

                    if (transactionFilterForm.requestSubmit) {
                        transactionFilterForm.requestSubmit();
                        return;
                    }

                    transactionFilterForm.submit();
                }

                const picker = flatpickr(transactionFilterDate, {
                    dateFormat: 'Y-m-d',
                    defaultDate: transactionFilterDate.value || null,
                    disableMobile: true,
                    allowInput: false,
                    clickOpens: true,
                    onChange: submitTransactionFilter,
                    onValueUpdate: submitTransactionFilter,
                });

                transactionFilterDate.addEventListener('click', function() {
                    picker.open();
                });

                transactionFilterDate.addEventListener('focus', function() {
                    picker.open();
                });

                return true;
            }

            if (!initTransactionDateFilter()) {
                window.addEventListener('load', function() {
                    if (initTransactionDateFilter()) {
                        return;
                    }

                    setTimeout(initTransactionDateFilter, 250);
                });
            }

            function formatCounterNumber(value) {
                return Number.isInteger(value) ?
                    value.toLocaleString() :
                    value.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
            }
        });
    </script>
@endsection
