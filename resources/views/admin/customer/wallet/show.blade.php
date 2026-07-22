@extends('admin.layouts.index')
@section('title', 'Wallet Top-Up Request')
@section('content')
    <style>
        .no-edit {
            cursor: not-allowed;
        }

        .topup-detail-page {
            color: #1f2937;
        }

        .topup-detail-page-title,
        .topup-detail-breadcrumb-current {
            color: #111827;
        }

        .topup-detail-breadcrumb-link {
            color: #4b5563;
        }

        .topup-detail-breadcrumb-link:hover {
            color: #1f2937;
        }

        .topup-detail-shell {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1fr) 320px;
        }

        .topup-detail-main,
        .topup-detail-sidebar {
            min-width: 0;
        }

        .topup-detail-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        }

        .topup-detail-card+.topup-detail-card {
            margin-top: 1.5rem;
        }

        .topup-detail-card-header {
            border-bottom: 1px solid #edf1f5;
            padding: 1.1rem 1.25rem;
        }

        .topup-detail-card-body {
            padding: 1.25rem;
        }

        .topup-detail-title {
            color: #111827;
            font-size: 1.2rem;
            font-weight: 680;
            margin: 0;
        }

        .topup-meta-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .topup-meta-panel {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            height: 100%;
            padding: 1rem;
        }

        .topup-meta-heading {
            color: #111827;
            font-size: 0.98rem;
            font-weight: 700;
            margin-bottom: 0.9rem;
        }

        .topup-meta-row+.topup-meta-row {
            margin-top: 0.85rem;
        }

        .topup-meta-label {
            color: #6b7280;
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
        }

        .topup-meta-value {
            color: #111827;
            font-size: 0.92rem;
            line-height: 1.5;
            word-break: break-word;
        }

        .topup-meta-value a {
            text-decoration: underline;
            text-underline-offset: 0.18rem;
        }

        .topup-tag {
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            font-size: 0.78rem;
            font-weight: 700;
            gap: 0.35rem;
            padding: 0.3rem 0.72rem;
        }

        .topup-tag i {
            font-size: 0.8rem;
        }

        .topup-tag-success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

        .topup-tag-warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .topup-tag-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .topup-tag-info {
            background: rgba(14, 165, 233, 0.12);
            color: #0369a1;
        }

        .topup-tag-primary {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .topup-actions-stack {
            display: grid;
            gap: 0.7rem;
        }

        .topup-action-block {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            padding: 0.8rem;
        }

        .topup-action-section-title {
            color: #111827;
            font-size: 0.95rem;
            font-weight: 750;
            margin-bottom: 0.7rem;
        }

        .topup-action-block h6 {
            color: #111827;
            font-size: 0.85rem;
            font-weight: 650;
            margin-bottom: 0.7rem;
        }

        .topup-action-topup-num {
            font-size: 0.78rem !important;
            font-weight: 560 !important;
        }

        .topup-action-caption {
            color: #6b7280;
            font-size: 0.78rem;
            margin-bottom: 0.7rem;
        }

        .topup-action-status {
            margin: -0.15rem 0 0.55rem;
        }

        .topup-action-buttons {
            display: grid;
            gap: 0.5rem;
        }

        .topup-action-item {
            margin-top: 0.55rem;
        }

        .topup-action-buttons form {
            margin: 0;
        }

        .topup-action-btn {
            border-radius: 0.5rem;
            font-size: 0.82rem;
            font-weight: 600;
            justify-content: center;
            padding: 0.55rem 0.75rem;
            width: 100%;
        }

        .topup-action-btn-solid {
            border: none;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
            color: #fff !important;
        }

        .topup-action-btn-solid:hover,
        .topup-action-btn-solid:focus {
            color: #fff !important;
        }

        .topup-action-btn-refund {
            background: #f59e0b;
            border: 1px solid #f59e0b;
            box-shadow: 0 10px 22px rgba(245, 158, 11, 0.28);
        }

        .topup-action-btn-refund:hover,
        .topup-action-btn-refund:focus {
            background: #d97706;
            border-color: #d97706;
            box-shadow: 0 12px 24px rgba(217, 119, 6, 0.34);
        }

        .topup-item-table td,
        .topup-item-table th {
            vertical-align: top;
        }

        .topup-item-name {
            color: #111827;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .topup-item-subtext {
            color: #6b7280;
            font-size: 0.78rem;
            margin-top: 0.35rem;
        }

        .topup-item-list {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .topup-item-list span,
        .topup-item-list a {
            font-size: 0.84rem;
        }

        .topup-summary-list {
            display: grid;
            gap: 0.85rem;
        }

        .topup-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .topup-summary-row strong {
            color: #111827;
            font-size: 0.88rem;
        }

        .topup-summary-row span {
            color: #6b7280;
            font-size: 0.84rem;
        }

        .back-to-list-link {
            align-items: center;
            background: #1456b8;
            border: 1px solid #1456b8;
            border-radius: 0.45rem;
            box-shadow: 0 10px 22px rgba(20, 86, 184, 0.2);
            color: #fff !important;
            display: inline-flex;
            font-size: 0.84rem;
            font-weight: 700;
            gap: 0.35rem;
            justify-content: center;
            min-width: 70px;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .back-to-list-link:hover,
        .back-to-list-link:focus {
            background: #0f4aa1;
            border-color: #0f4aa1;
            box-shadow: 0 12px 24px rgba(20, 86, 184, 0.28);
            color: #fff !important;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .top-up-modal {
            overflow: hidden;
            border: 0;
            border-radius: 12px;
        }

        .top-up-modal .modal-header {
            padding: 20px 22px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
        }

        .top-up-modal .modal-body {
            padding: 22px;
        }

        .top-up-modal .modal-footer {
            padding: 16px 22px;
            border-top: 1px solid rgba(148, 163, 184, 0.15);
        }

        .top-up-modal-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 10px;
            color: #0d6efd;
            font-size: 21px;
            background: rgba(13, 110, 253, 0.12);
        }

        .top-up-customer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 16px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 9px;
            background: rgba(148, 163, 184, 0.035);
        }

        .top-up-input-group .input-group-text {
            min-width: 65px;
            justify-content: center;
            border-color: rgba(13, 110, 253, 0.3);
            font-weight: 600;
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.07);
        }

        .top-up-input-group .form-control {
            height: 50px;
            border-color: rgba(13, 110, 253, 0.3);
            font-size: 18px;
            font-weight: 600;
        }

        .top-up-input-group .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        html[data-bs-theme="dark"] .topup-detail-page {
            color: #d6e0f0;
        }

        html[data-bs-theme="dark"] .topup-detail-page-title,
        html[data-bs-theme="dark"] .topup-detail-breadcrumb-current {
            color: #e8eef9;
        }

        html[data-bs-theme="dark"] .topup-detail-breadcrumb-link {
            color: #9fb1cc;
        }

        html[data-bs-theme="dark"] .topup-detail-breadcrumb-link:hover {
            color: #dce7f8;
        }

        html[data-bs-theme="dark"] .topup-detail-card,
        html[data-bs-theme="dark"] .topup-meta-panel,
        html[data-bs-theme="dark"] .topup-action-block,
        html[data-bs-theme="dark"] .transaction-action-block {
            background: #252935;
            border-color: #394051;
            box-shadow: 0 16px 32px rgba(2, 6, 23, 0.24);
        }

        html[data-bs-theme="dark"] .topup-detail-card-header {
            background: #2b3040;
            border-bottom-color: #394051;
        }

        html[data-bs-theme="dark"] .topup-detail-title,
        html[data-bs-theme="dark"] .topup-meta-heading,
        html[data-bs-theme="dark"] .topup-action-section-title,
        html[data-bs-theme="dark"] .topup-action-block h6,
        html[data-bs-theme="dark"] .topup-item-name,
        html[data-bs-theme="dark"] .topup-summary-row strong,
        html[data-bs-theme="dark"] .table.topup-item-table tbody td {
            color: #f3f7ff;
        }

        html[data-bs-theme="dark"] .topup-meta-label,
        html[data-bs-theme="dark"] .topup-action-caption,
        html[data-bs-theme="dark"] .topup-item-subtext,
        html[data-bs-theme="dark"] .topup-summary-row span,
        html[data-bs-theme="dark"] .text-muted,
        html[data-bs-theme="dark"] .small.text-muted {
            color: #9fb1cc !important;
        }

        html[data-bs-theme="dark"] .topup-meta-value,
        html[data-bs-theme="dark"] .topup-item-list span,
        html[data-bs-theme="dark"] .topup-item-list a {
            color: #dbe7f8;
        }

        html[data-bs-theme="dark"] .topup-meta-value a,
        html[data-bs-theme="dark"] .topup-item-list a {
            color: #7dd3fc;
        }

        html[data-bs-theme="dark"] .topup-meta-value a:hover,
        html[data-bs-theme="dark"] .topup-item-list a:hover {
            color: #bae6fd;
        }

        html[data-bs-theme="dark"] .table.topup-item-table,
        html[data-bs-theme="dark"] .table.topup-item-table tbody,
        html[data-bs-theme="dark"] .table.topup-item-table tbody tr,
        html[data-bs-theme="dark"] .table.topup-item-table tbody td {
            background: #252935;
            border-color: #394051;
        }

        html[data-bs-theme="dark"] .table.topup-item-table thead.bg-light {
            background: #32394a !important;
        }

        html[data-bs-theme="dark"] .table.topup-item-table thead th,
        html[data-bs-theme="dark"] .table.topup-item-table thead tr {
            color: #9fb1cc;
            border-color: #445066;
        }

        html[data-bs-theme="dark"] .table.topup-item-table.table-hover tbody tr:hover>* {
            background: #2d3444;
            color: inherit;
        }

        html[data-bs-theme="dark"] .topup-tag-warning {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
        }

        html[data-bs-theme="dark"] .topup-tag-success {
            background: rgba(34, 197, 94, 0.16);
            color: #4ade80;
        }

        html[data-bs-theme="dark"] .topup-tag-danger {
            background: rgba(239, 68, 68, 0.16);
            color: #f87171;
        }

        html[data-bs-theme="dark"] .topup-tag-info {
            background: rgba(56, 189, 248, 0.16);
            color: #7dd3fc;
        }

        html[data-bs-theme="dark"] .topup-tag-primary {
            background: rgba(96, 165, 250, 0.16);
            color: #93c5fd;
        }

        html[data-bs-theme="dark"] .btn-secondary.topup-action-btn-solid {
            background: #4b5563;
            border-color: #4b5563;
        }

        html[data-bs-theme="dark"] .btn-info.topup-action-btn-solid {
            background: #0891b2;
            border-color: #0891b2;
        }

        html[data-bs-theme="dark"] .btn-warning.topup-action-btn-solid {
            background: #d97706;
            border-color: #d97706;
            color: #fff !important;
        }

        html[data-bs-theme="dark"] .transaction-tag-success {
            background: rgba(34, 197, 94, 0.16);
            color: #4ade80;
        }

        html[data-bs-theme="dark"] .transaction-tag-warning {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
        }

        .transaction-tag-warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .transaction-action-block {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            padding: 0.8rem;
        }

        .transaction-action-item {
            margin-top: 0.55rem;
        }

        .transaction-action-status {
            margin: -0.15rem 0 0.55rem;
        }

        .transaction-tag {
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            font-size: 0.78rem;
            font-weight: 700;
            gap: 0.35rem;
            padding: 0.3rem 0.72rem;
        }

        .transaction-tag i {
            font-size: 0.8rem;
        }

        .transaction-tag-success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

        @media (max-width: 1199.98px) {
            .topup-detail-shell {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .topup-meta-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="container-fluid topup-detail-page">
        @include('components.alert')

        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 topup-detail-page-title">Wallet Top-Up Request</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="" class="topup-detail-breadcrumb-link">Top Up Request</a>
                    </li>
                    <li class="breadcrumb-item active topup-detail-breadcrumb-current"></li>
                </ol>
            </div>
            <div class="py-3">
                <a href="{{ route('customer.wallet.index', $customer->id) }}" class="back-to-list-link">
                    Back
                </a>
            </div>
        </div>

        <div class="topup-detail-shell">
            <div class="topup-detail-main">
                <section class="topup-detail-card">
                    <div class="topup-detail-card-header">
                        <h1 class="topup-detail-title">Wallet Top Up Request</h1>
                    </div>
                    <div class="topup-detail-card-body">
                        <div class="topup-meta-grid">
                            <div class="topup-meta-panel">
                                <div class="topup-meta-heading">Customer</div>
                                <div class="topup-meta-row">
                                    <span class="topup-meta-label">Name</span>
                                    <div class="topup-meta-value">{{ $customer->name }}</div>
                                </div>
                                <div class="topup-meta-row">
                                    <span class="topup-meta-label">Email</span>
                                    <div class="topup-meta-value">
                                        {{ $customer->email }}
                                    </div>
                                </div>
                                <div class="topup-meta-row">
                                    <span class="topup-meta-label">Phone</span>
                                    <div class="topup-meta-value">{{ $customer->phone ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="topup-meta-panel">
                                <div class="topup-meta-heading">Request Summary</div>
                                <div class="topup-meta-row">
                                    <span class="topup-meta-label">Request Balance</span>
                                    <div class="topup-meta-value">{{ number_format($transaction->amount) }} MMK</div>
                                </div>
                                <div class="topup-meta-row">
                                    <span class="topup-meta-label">Remaining Balance</span>
                                    <div class="topup-meta-value fw-semibold">
                                        {{ number_format(optional($customer->customerWallet)->balance) ?? '-' }} MMK
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="topup-detail-sidebar">
                <section class="topup-detail-card">
                    <div class="topup-detail-card-header">
                        <h5 class="mb-0">Request Actions</h5>
                    </div>
                    <div class="topup-detail-card-body">
                        <div class="topup-actions-stack">
                            <div class="topup-action-block">
                                <h6 class="topup-action-section-title">User Actions</h6>
                                <div class="topup-action-buttons">
                                    @if ($transaction->payment_slip)
                                        <a href="{{ asset('storage/' . $transaction->payment_slip) }}" target="_blank"
                                            rel="noopener"
                                            class="btn btn-secondary topup-action-btn topup-action-btn-solid">
                                            <i class="ti ti-photo"></i> View Slip
                                        </a>
                                    @endif

                                    @php
                                        $isApproved = $transaction->transaction_state == 'approved';
                                    @endphp
                                    @if (!$isApproved)
                                        <button data-bs-toggle="modal" data-bs-target="#top-up-box" type="submit"
                                            class="btn btn-success topup-action-btn topup-action-btn-solid">
                                            <i class="ti ti-circle-check"></i> Approve Top-Up Request
                                        </button>
                                    @endif
                                    <div class="transaction-action-block transaction-action-item">
                                        <h6 class="transaction-action-order-num">Transaction State</h6>
                                        <div class="transaction-action-status">
                                            <span
                                                class="transaction-tag transaction-tag-{{ $isApproved ? 'success' : 'warning' }}">
                                                <i
                                                    class="ti ti-point-filled"></i>{{ $isApproved ? 'Payment Approved' : 'Pending Payment' }}
                                            </span>
                                        </div>
                                        <div class="transaction-action-buttons">
                                            <div class="text-muted small">No user actions available.</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                </section>
            </aside>
        </div>
    </div>

    <div class="modal fade" id="top-up-box" tabindex="-1" aria-labelledby="topUpModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content top-up-modal">

                <form action="{{ route('customer.wallet.update', $transaction->id) }}" method="POST">
                    @csrf
                    @method('patch')
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-3">

                            <div class="top-up-modal-icon">
                                <i class="ti ti-wallet"></i>
                            </div>

                            <div>
                                <h5 class="modal-title mb-1" id="topUpModalLabel">
                                    Top Up Wallet
                                </h5>

                                <p class="text-muted small mb-0">
                                    Enter the amount to add to the customer wallet.
                                </p>
                            </div>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="top-up-customer mb-4">
                            <div>
                                <span class="text-muted small">
                                    Customer
                                </span>

                                <h6 class="mb-0 mt-1">
                                    {{ $customer->name }}
                                </h6>
                            </div>

                            <div class="text-end">
                                <span class="text-muted small">
                                    Remaining Balance
                                </span>

                                <h6 class="mb-0 mt-1 text-primary">
                                    {{ number_format($customer->customerWallet?->balance) ?? '-' }} MMK
                                </h6>
                            </div>
                        </div>

                        <input type="hidden" name="customer_id" value="{{ $customer->id ?? '' }}">

                        <div>
                            <label for="topUpAmount" class="form-label fw-semibold">
                                Top Up Amount
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group top-up-input-group">
                                <span class="input-group-text">
                                    MMK
                                </span>

                                <input type="text" value="{{ number_format((int) $transaction->amount) }}"
                                    id="topUpAmount" class="form-control no-edit" value="{{ old('amount') }}"
                                    placeholder="Enter amount" readonly>
                            </div>

                            @error('amount')
                                <small class="text-danger d-block mt-1">
                                    {{ $message }}
                                </small>
                            @enderror

                            <small class="text-muted d-block mt-2">
                                Minimum top-up amount is 1,000 MMK.
                            </small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ti ti-wallet me-1"></i>
                            Top Up
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
