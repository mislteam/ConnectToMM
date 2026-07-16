@extends('admin.layouts.index')
@section('title', 'Order Details')
@section('content')
    <style>
        .order-detail-page {
            color: #1f2937;
        }

        .order-detail-page-title,
        .order-detail-breadcrumb-current {
            color: #111827;
        }

        .order-detail-breadcrumb-link {
            color: #4b5563;
        }

        .order-detail-breadcrumb-link:hover {
            color: #1f2937;
        }

        .order-detail-shell {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1fr) 320px;
        }

        .order-detail-main,
        .order-detail-sidebar {
            min-width: 0;
        }

        .order-detail-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        }

        .order-detail-card+.order-detail-card {
            margin-top: 1.5rem;
        }

        .order-detail-card-header {
            border-bottom: 1px solid #edf1f5;
            padding: 1.1rem 1.25rem;
        }

        .order-detail-card-body {
            padding: 1.25rem;
        }

        .order-detail-title {
            color: #111827;
            font-size: 1.45rem;
            font-weight: 680;
            margin: 0;
        }

        .order-meta-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .order-meta-panel {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            height: 100%;
            padding: 1rem;
        }

        .order-meta-heading {
            color: #111827;
            font-size: 0.98rem;
            font-weight: 700;
            margin-bottom: 0.9rem;
        }

        .order-meta-row+.order-meta-row {
            margin-top: 0.85rem;
        }

        .order-meta-label {
            color: #6b7280;
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
        }

        .order-meta-value {
            color: #111827;
            font-size: 0.92rem;
            line-height: 1.5;
            word-break: break-word;
        }

        .order-meta-value a {
            text-decoration: underline;
            text-underline-offset: 0.18rem;
        }

        .order-tag {
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            font-size: 0.78rem;
            font-weight: 700;
            gap: 0.35rem;
            padding: 0.3rem 0.72rem;
        }

        .order-tag i {
            font-size: 0.8rem;
        }

        .order-tag-success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

        .order-tag-warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .order-tag-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .order-tag-info {
            background: rgba(14, 165, 233, 0.12);
            color: #0369a1;
        }

        .order-tag-primary {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .order-actions-stack {
            display: grid;
            gap: 0.7rem;
        }

        .order-action-block {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            padding: 0.8rem;
        }

        .order-action-section-title {
            color: #111827;
            font-size: 0.95rem;
            font-weight: 750;
            margin-bottom: 0.7rem;
        }

        .order-action-block h6 {
            color: #111827;
            font-size: 0.85rem;
            font-weight: 650;
            margin-bottom: 0.7rem;
        }

        .order-action-order-num {
            font-size: 0.78rem !important;
            font-weight: 560 !important;
        }

        .order-action-caption {
            color: #6b7280;
            font-size: 0.78rem;
            margin-bottom: 0.7rem;
        }

        .order-action-status {
            margin: -0.15rem 0 0.55rem;
        }

        .order-action-buttons {
            display: grid;
            gap: 0.5rem;
        }

        .order-action-item {
            margin-top: 0.55rem;
        }

        .order-action-buttons form {
            margin: 0;
        }

        .order-action-btn {
            border-radius: 0.5rem;
            font-size: 0.82rem;
            font-weight: 600;
            justify-content: center;
            padding: 0.55rem 0.75rem;
            width: 100%;
        }

        .order-action-btn-solid {
            border: none;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
            color: #fff !important;
        }

        .order-action-btn-solid:hover,
        .order-action-btn-solid:focus {
            color: #fff !important;
        }

        .order-action-btn-refund {
            background: #f59e0b;
            border: 1px solid #f59e0b;
            box-shadow: 0 10px 22px rgba(245, 158, 11, 0.28);
        }

        .order-action-btn-refund:hover,
        .order-action-btn-refund:focus {
            background: #d97706;
            border-color: #d97706;
            box-shadow: 0 12px 24px rgba(217, 119, 6, 0.34);
        }

        .order-item-table td,
        .order-item-table th {
            vertical-align: top;
        }

        .order-item-name {
            color: #111827;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .order-item-subtext {
            color: #6b7280;
            font-size: 0.78rem;
            margin-top: 0.35rem;
        }

        .order-item-list {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .order-item-list span,
        .order-item-list a {
            font-size: 0.84rem;
        }

        .order-summary-list {
            display: grid;
            gap: 0.85rem;
        }

        .order-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .order-summary-row strong {
            color: #111827;
            font-size: 0.88rem;
        }

        .order-summary-row span {
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

        html[data-bs-theme="dark"] .order-detail-page {
            color: #d6e0f0;
        }

        html[data-bs-theme="dark"] .order-detail-page-title,
        html[data-bs-theme="dark"] .order-detail-breadcrumb-current {
            color: #e8eef9;
        }

        html[data-bs-theme="dark"] .order-detail-breadcrumb-link {
            color: #9fb1cc;
        }

        html[data-bs-theme="dark"] .order-detail-breadcrumb-link:hover {
            color: #dce7f8;
        }

        html[data-bs-theme="dark"] .order-detail-card,
        html[data-bs-theme="dark"] .order-meta-panel,
        html[data-bs-theme="dark"] .order-action-block {
            background: #252935;
            border-color: #394051;
            box-shadow: 0 16px 32px rgba(2, 6, 23, 0.24);
        }

        html[data-bs-theme="dark"] .order-detail-card-header {
            background: #2b3040;
            border-bottom-color: #394051;
        }

        html[data-bs-theme="dark"] .order-detail-title,
        html[data-bs-theme="dark"] .order-meta-heading,
        html[data-bs-theme="dark"] .order-action-section-title,
        html[data-bs-theme="dark"] .order-action-block h6,
        html[data-bs-theme="dark"] .order-item-name,
        html[data-bs-theme="dark"] .order-summary-row strong,
        html[data-bs-theme="dark"] .table.order-item-table tbody td {
            color: #f3f7ff;
        }

        html[data-bs-theme="dark"] .order-meta-label,
        html[data-bs-theme="dark"] .order-action-caption,
        html[data-bs-theme="dark"] .order-item-subtext,
        html[data-bs-theme="dark"] .order-summary-row span,
        html[data-bs-theme="dark"] .text-muted,
        html[data-bs-theme="dark"] .small.text-muted {
            color: #9fb1cc !important;
        }

        html[data-bs-theme="dark"] .order-meta-value,
        html[data-bs-theme="dark"] .order-item-list span,
        html[data-bs-theme="dark"] .order-item-list a {
            color: #dbe7f8;
        }

        html[data-bs-theme="dark"] .order-meta-value a,
        html[data-bs-theme="dark"] .order-item-list a {
            color: #7dd3fc;
        }

        html[data-bs-theme="dark"] .order-meta-value a:hover,
        html[data-bs-theme="dark"] .order-item-list a:hover {
            color: #bae6fd;
        }

        html[data-bs-theme="dark"] .table.order-item-table,
        html[data-bs-theme="dark"] .table.order-item-table tbody,
        html[data-bs-theme="dark"] .table.order-item-table tbody tr,
        html[data-bs-theme="dark"] .table.order-item-table tbody td {
            background: #252935;
            border-color: #394051;
        }

        html[data-bs-theme="dark"] .table.order-item-table thead.bg-light {
            background: #32394a !important;
        }

        html[data-bs-theme="dark"] .table.order-item-table thead th,
        html[data-bs-theme="dark"] .table.order-item-table thead tr {
            color: #9fb1cc;
            border-color: #445066;
        }

        html[data-bs-theme="dark"] .table.order-item-table.table-hover tbody tr:hover>* {
            background: #2d3444;
            color: inherit;
        }

        html[data-bs-theme="dark"] .order-tag-warning {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
        }

        html[data-bs-theme="dark"] .order-tag-success {
            background: rgba(34, 197, 94, 0.16);
            color: #4ade80;
        }

        html[data-bs-theme="dark"] .order-tag-danger {
            background: rgba(239, 68, 68, 0.16);
            color: #f87171;
        }

        html[data-bs-theme="dark"] .order-tag-info {
            background: rgba(56, 189, 248, 0.16);
            color: #7dd3fc;
        }

        html[data-bs-theme="dark"] .order-tag-primary {
            background: rgba(96, 165, 250, 0.16);
            color: #93c5fd;
        }

        html[data-bs-theme="dark"] .btn-secondary.order-action-btn-solid {
            background: #4b5563;
            border-color: #4b5563;
        }

        html[data-bs-theme="dark"] .btn-info.order-action-btn-solid {
            background: #0891b2;
            border-color: #0891b2;
        }

        html[data-bs-theme="dark"] .btn-warning.order-action-btn-solid {
            background: #d97706;
            border-color: #d97706;
            color: #fff !important;
        }

        @media (max-width: 1199.98px) {
            .order-detail-shell {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .order-meta-grid {
                grid-template-columns: 1fr;
            }
        }

        /* for payment */

        .payment-action-dropdown {
            position: relative;
        }

        .payment-action-dropdown .order-action-btn {
            height: 38px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .payment-action-menu {
            min-width: 100% !important;
            width: 100% !important;
            /* margin-top: 6px !important; */
            padding: 6px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .payment-action-menu .payment-action-item {
            width: 100%;
            height: 30px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-action-menu .payment-action-item:hover {
            background-color: #f8fafc;
        }

        .payment-action-menu form {
            margin: 0;
        }
    </style>

    @php
        $primaryOrder = $summary['primary_order'] ?? null;
        $paymentSlipPath = $summary['payment_slip_path'] ?? null;
        $paymentSlipUrl = $paymentSlipPath ? \Illuminate\Support\Facades\Storage::url($paymentSlipPath) : null;
        $canApprovePayment =
            $primaryOrder && (int) $primaryOrder->our_status === \App\Models\RoamOrder::OUR_STATUS_PENDING_PAYMENT;
        $canAdminCancel = $canApprovePayment;
        $isGroupRoamRefunded = (bool) ($summary['has_roam_refund'] ?? false);
        $statusTagClass = match ($summary['status_key'] ?? null) {
            'completed' => 'order-tag-success',
            'pending_payment' => 'order-tag-warning',
            'failed', 'admin_cancelled', 'cancelled' => 'order-tag-danger',
            'refunded' => 'order-tag-info',
            default => 'order-tag-primary',
        };
        $paymentTagClass = match ($summary['payment_label'] ?? null) {
            'Paid' => 'order-tag-success',
            'Pending' => 'order-tag-warning',
            'Admin Cancel', 'Cancelled' => 'order-tag-danger',
            'Refunded' => 'order-tag-info',
            default => 'order-tag-primary',
        };
    @endphp

    <div class="container-fluid order-detail-page">
        @include('components.alert')

        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 order-detail-page-title">Order Details</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('order.index') }}"
                            class="order-detail-breadcrumb-link">Orders</a></li>
                    <li class="breadcrumb-item active order-detail-breadcrumb-current">{{ $summary['reference'] }}</li>
                </ol>
            </div>
            <div class="py-3">
                <a href="{{ route('order.index') }}" class="back-to-list-link">
                    Back
                </a>
            </div>
        </div>

        <div class="order-detail-shell">
            <div class="order-detail-main">
                <section class="order-detail-card">
                    <div class="order-detail-card-header">
                        <h1 class="order-detail-title">Order details</h1>
                    </div>
                    <div class="order-detail-card-body">
                        <div class="order-meta-grid">
                            <div class="order-meta-panel">
                                <div class="order-meta-heading">General</div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Order ID</span>
                                    <div class="order-meta-value">{{ $summary['reference'] }}</div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Date created</span>
                                    <div class="order-meta-value">
                                        {{ optional($summary['created_at'])->format('Y-m-d') ?? '-' }}
                                    </div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Status</span>
                                    <div class="order-meta-value">
                                        <span class="order-tag {{ $statusTagClass }}">
                                            <i class="ti ti-point-filled"></i> {{ $summary['status_label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="order-meta-panel">
                                <div class="order-meta-heading">Customer</div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Name</span>
                                    <div class="order-meta-value">{{ $summary['customer_name'] }}</div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Email</span>
                                    <div class="order-meta-value">
                                        @if (!empty($summary['customer_email']) && $summary['customer_email'] !== '-')
                                            <a
                                                href="mailto:{{ $summary['customer_email'] }}">{{ $summary['customer_email'] }}</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Phone</span>
                                    <div class="order-meta-value">{{ $primaryOrder?->customer?->phone ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="order-meta-panel">
                                <div class="order-meta-heading">Order Summary</div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Products</span>
                                    <div class="order-meta-value">
                                        {!! nl2br(e($summary['product_summary'] ?: '-')) !!}</div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Order total</span>
                                    <div class="order-meta-value fw-semibold">
                                        {{ number_format((float) $summary['amount']) }} MMK
                                    </div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Payment Method</span>
                                    <div class="order-meta-value fw-semibold">
                                        {{ ucwords(str_replace('_', ' ', optional($summary['primary_order'])->payment_method ?? '-')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="order-detail-card">
                    <div class="order-detail-card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover w-100 mb-0 order-item-table">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>Roam Order ID</th>
                                    <th>Product</th>
                                    <th>Service Type</th>
                                    <th>ICCID</th>
                                    <th>PDF</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        $isRoamApiFailed =
                                            (int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_API_FAILED;
                                        $isRoamApiSuccess =
                                            (int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_COMPLETED;
                                        $isRoamNormal =
                                            (int) $order->roam_status === \App\Models\RoamOrder::ROAM_STATUS_NORMAL;
                                        $isRoamCancelled =
                                            (int) $order->roam_status === \App\Models\RoamOrder::ROAM_STATUS_CANCELLED;
                                        $isRoamRefunded = $isRoamApiSuccess && $isRoamCancelled;
                                        $statusLabel = $isRoamRefunded
                                            ? 'Roam Refunded'
                                            : ((int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_REFUNDED
                                                ? 'Refunded'
                                                : \App\Models\RoamOrder::OUR_STATUS_LABELS[(int) $order->our_status] ??
                                                    (string) $order->our_status);
                                        $statusTagClass = $isRoamRefunded
                                            ? 'order-tag-info'
                                            : match ((int) $order->our_status) {
                                                \App\Models\RoamOrder::OUR_STATUS_COMPLETED => 'order-tag-success',
                                                \App\Models\RoamOrder::OUR_STATUS_PENDING_PAYMENT
                                                    => 'order-tag-warning',
                                                \App\Models\RoamOrder::OUR_STATUS_API_FAILED,
                                                \App\Models\RoamOrder::OUR_STATUS_ADMIN_CANCELLED,
                                                \App\Models\RoamOrder::OUR_STATUS_CANCELLED
                                                    => 'order-tag-danger',
                                                \App\Models\RoamOrder::OUR_STATUS_REFUNDED => 'order-tag-info',
                                                default => 'order-tag-primary',
                                            };
                                        $iccids = $order->items?->pluck('iccid')->filter()->values() ?? collect();
                                        $pdfUrls = $order->items?->pluck('pdf_url')->filter()->values() ?? collect();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="order-item-name">{{ $order->roam_order_num }}</div>
                                            <div class="order-item-subtext">Qty: {{ max(1, (int) $order->quantity) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="order-item-name">
                                                {{ $order->formatted_product_name ?: ($order->remark ?: ($order->sku_id ?: '-')) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="order-item-name">
                                                {{ \Illuminate\Support\Str::headline((string) $order->service_type) ?: '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($iccids->isEmpty())
                                                <span class="text-muted">-</span>
                                            @else
                                                <div class="order-item-list">
                                                    @foreach ($iccids as $iccid)
                                                        <span>{{ $iccid }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($pdfUrls->isEmpty())
                                                <span class="text-muted">-</span>
                                            @else
                                                <div class="order-item-list">
                                                    @foreach ($pdfUrls as $url)
                                                        <a href="{{ $url }}" target="_blank"
                                                            rel="noopener">Open
                                                            PDF</a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="order-item-name">
                                                {{ number_format((float) $order->billable_total_price) }} MMK
                                            </div>
                                        </td>
                                        <td>
                                            <span class="order-tag {{ $statusTagClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $statusLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="order-detail-sidebar">
                <section class="order-detail-card">
                    <div class="order-detail-card-header">
                        <h5 class="mb-0">Order Actions</h5>
                    </div>
                    <div class="order-detail-card-body">
                        <div class="order-actions-stack">
                            <div class="order-action-block">
                                <h6 class="order-action-section-title">User Actions</h6>
                                <div class="order-action-buttons">
                                    @if ($paymentSlipUrl)
                                        <a href="{{ $paymentSlipUrl }}" target="_blank" rel="noopener"
                                            class="btn btn-secondary order-action-btn order-action-btn-solid">
                                            <i class="ti ti-photo"></i> View Slip
                                        </a>
                                    @endif

                                    {{-- @if ($canApprovePayment)
                                        <form method="POST"
                                            action="{{ route('order.approve-payment', ['roamOrder' => $primaryOrder->id]) }}"
                                            onsubmit="return confirm('Approve payment for this order reference and start provisioning?');">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-success order-action-btn order-action-btn-solid">
                                                <i class="ti ti-circle-check"></i> Approve Payment
                                            </button>
                                        </form>
                                    @endif

                                    @if ($canAdminCancel)
                                        <form method="POST"
                                            action="{{ route('order.cancel-payment', ['roamOrder' => $primaryOrder->id]) }}"
                                            onsubmit="return confirm('Cancel this pending payment order? The customer will be notified.');">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-danger order-action-btn order-action-btn-solid">
                                                <i class="ti ti-x"></i> Admin Cancel
                                            </button>
                                        </form>
                                    @endif --}}

                                    @if ($canApprovePayment || $canAdminCancel)
                                        <div class="payment-action-dropdown dropdown w-100">
                                            <button
                                                class="btn btn-success dropdown-toggle order-action-btn order-action-btn-solid w-100"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{-- <i class="ti ti-settings"></i>  --}}
                                                Payment Actions
                                            </button>

                                            <ul class="dropdown-menu payment-action-menu w-100">
                                                @if ($canApprovePayment)
                                                    <li>
                                                        <form method="POST"
                                                            action="{{ route('order.approve-payment', ['roamOrder' => $primaryOrder->id]) }}"
                                                            onsubmit="return confirm('Approve payment for this Roam order reference?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="dropdown-item payment-action-item text-success">
                                                                <i class="ti ti-circle-check"></i>
                                                                <span>Approve Payment</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if ($canApprovePayment && $canAdminCancel)
                                                    <li>
                                                        <hr class="dropdown-divider my-1">
                                                    </li>
                                                @endif

                                                @if ($canAdminCancel)
                                                    <li>
                                                        <form method="POST"
                                                            action="{{ route('order.cancel-payment', ['roamOrder' => $primaryOrder->id]) }}"
                                                            onsubmit="return confirm('Cancel this Roam order by admin?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="dropdown-item payment-action-item text-danger">
                                                                <i class="ti ti-circle-x"></i>
                                                                <span>Admin Cancel</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif

                                    @if (!$paymentSlipUrl && !$canApprovePayment)
                                        <div class="text-muted small"></div>
                                    @endif
                                </div>

                                @foreach ($orders as $order)
                                    @php
                                        $isRoamApiFailed =
                                            (int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_API_FAILED;
                                        $isRoamApiSuccess =
                                            (int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_COMPLETED;
                                        $isRoamCancelled =
                                            (int) $order->roam_status === \App\Models\RoamOrder::ROAM_STATUS_CANCELLED;
                                        $isRoamRefunded = $isRoamApiSuccess && $isRoamCancelled;
                                        $hasRoamRefund =
                                            (bool) data_get($order->raw_response, 'refund.roam_api.response') ||
                                            $isRoamRefunded;
                                        $hasInternalRefund = (bool) data_get(
                                            $order->raw_response,
                                            'refund.internal_payment.response',
                                        );
                                        $canInternalRefund = $isRoamApiFailed;
                                        $canRoamPaymentRefund =
                                            $isRoamApiSuccess && $hasRoamRefund && !$hasInternalRefund;
                                        $showRoamRefundAction =
                                            $isRoamApiSuccess &&
                                            (int) $order->our_status !== \App\Models\RoamOrder::OUR_STATUS_REFUNDED;
                                        $actionStatusLabel = $isRoamRefunded
                                            ? 'Roam Refunded'
                                            : ((int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_REFUNDED
                                                ? 'Refunded'
                                                : \App\Models\RoamOrder::OUR_STATUS_LABELS[(int) $order->our_status] ??
                                                    (string) $order->our_status);
                                        $actionStatusTagClass = $isRoamRefunded
                                            ? 'order-tag-info'
                                            : match ((int) $order->our_status) {
                                                \App\Models\RoamOrder::OUR_STATUS_COMPLETED => 'order-tag-success',
                                                \App\Models\RoamOrder::OUR_STATUS_PENDING_PAYMENT
                                                    => 'order-tag-warning',
                                                \App\Models\RoamOrder::OUR_STATUS_API_FAILED,
                                                \App\Models\RoamOrder::OUR_STATUS_ADMIN_CANCELLED,
                                                \App\Models\RoamOrder::OUR_STATUS_CANCELLED
                                                    => 'order-tag-danger',
                                                \App\Models\RoamOrder::OUR_STATUS_REFUNDED => 'order-tag-info',
                                                default => 'order-tag-primary',
                                            };
                                    @endphp

                                    <div class="order-action-block order-action-item">
                                        <h6 class="order-action-order-num">{{ $order->roam_order_num }}</h6>
                                        <div class="order-action-status">
                                            <span class="order-tag {{ $actionStatusTagClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $actionStatusLabel }}
                                            </span>
                                        </div>
                                        <div class="order-action-buttons">
                                            @if ($canInternalRefund)
                                                <form method="POST"
                                                    action="{{ route('order.refund', ['roamOrder' => $order->id]) }}"
                                                    onsubmit="return confirm('Mark payment refund for this order?');">
                                                    @csrf
                                                    <input type="hidden" name="refund_method"
                                                        value="{{ \App\Models\RoamOrder::REFUND_METHOD_INTERNAL_PAYMENT }}">
                                                    <button type="submit"
                                                        class="btn btn-info order-action-btn order-action-btn-solid">
                                                        <i class="ti ti-cash"></i> Payment Refund
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canRoamPaymentRefund)
                                                <form method="POST"
                                                    action="{{ route('order.refund', ['roamOrder' => $order->id]) }}"
                                                    onsubmit="return confirm('Mark customer payment refund for this Roam-refunded order?');">
                                                    @csrf
                                                    <input type="hidden" name="refund_method"
                                                        value="{{ \App\Models\RoamOrder::REFUND_METHOD_INTERNAL_PAYMENT }}">
                                                    <button type="submit"
                                                        class="btn btn-info order-action-btn order-action-btn-solid">
                                                        <i class="ti ti-cash"></i> Payment Refund
                                                    </button>
                                                </form>
                                            @elseif ($showRoamRefundAction && $hasInternalRefund)
                                                <div class="text-muted small">Payment refund already completed.</div>
                                            @endif

                                            @if (!$canInternalRefund && !$canRoamPaymentRefund)
                                                <div class="text-muted small">No user actions available.</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="order-action-block">
                                <h6 class="order-action-section-title">Roam Portal Actions</h6>

                                @foreach ($orders as $order)
                                    @php
                                        $isRoamApiFailed =
                                            (int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_API_FAILED;
                                        $isRoamApiSuccess =
                                            (int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_COMPLETED;
                                        $isRoamNormal =
                                            (int) $order->roam_status === \App\Models\RoamOrder::ROAM_STATUS_NORMAL;
                                        $isRoamCancelled =
                                            (int) $order->roam_status === \App\Models\RoamOrder::ROAM_STATUS_CANCELLED;
                                        $isRoamRefunded = $isRoamApiSuccess && $isRoamCancelled;
                                        $hasRoamRefund =
                                            (bool) data_get($order->raw_response, 'refund.roam_api.response') ||
                                            $isRoamRefunded;
                                        $roamRefundExpiresAt = $order->purchase_date?->copy()->addDays(90);
                                        $isWithinRoamRefundWindow =
                                            $roamRefundExpiresAt !== null && !$roamRefundExpiresAt->isPast();
                                        $canRoamRefund =
                                            $isRoamApiSuccess &&
                                            $isRoamNormal &&
                                            $isWithinRoamRefundWindow &&
                                            !$hasRoamRefund &&
                                            !str_starts_with((string) $order->roam_order_num, 'TMP-');
                                        $showRoamRefundAction =
                                            $isRoamApiSuccess &&
                                            (int) $order->our_status !== \App\Models\RoamOrder::OUR_STATUS_REFUNDED;
                                        $canRetryThisOrder =
                                            $isRoamApiFailed && ($summary['payment_label'] ?? null) === 'Paid';
                                        $actionStatusLabel =
                                            $isGroupRoamRefunded || $hasRoamRefund || $isRoamRefunded
                                                ? 'Roam Refunded'
                                                : ((int) $order->our_status ===
                                                \App\Models\RoamOrder::OUR_STATUS_REFUNDED
                                                    ? 'Refunded'
                                                    : \App\Models\RoamOrder::OUR_STATUS_LABELS[
                                                            (int) $order->our_status
                                                        ] ?? (string) $order->our_status);
                                        $actionStatusTagClass =
                                            $isGroupRoamRefunded || $hasRoamRefund || $isRoamRefunded
                                                ? 'order-tag-info'
                                                : match ((int) $order->our_status) {
                                                    \App\Models\RoamOrder::OUR_STATUS_COMPLETED => 'order-tag-success',
                                                    \App\Models\RoamOrder::OUR_STATUS_PENDING_PAYMENT
                                                        => 'order-tag-warning',
                                                    \App\Models\RoamOrder::OUR_STATUS_API_FAILED,
                                                    \App\Models\RoamOrder::OUR_STATUS_ADMIN_CANCELLED,
                                                    \App\Models\RoamOrder::OUR_STATUS_CANCELLED
                                                        => 'order-tag-danger',
                                                    \App\Models\RoamOrder::OUR_STATUS_REFUNDED => 'order-tag-info',
                                                    default => 'order-tag-primary',
                                                };
                                    @endphp

                                    <div class="order-action-block order-action-item">
                                        <h6 class="order-action-order-num">{{ $order->roam_order_num }}</h6>
                                        <div class="order-action-status">
                                            <span class="order-tag {{ $actionStatusTagClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $actionStatusLabel }}
                                            </span>
                                        </div>
                                        <div class="order-action-buttons">
                                            @if ($canRetryThisOrder)
                                                <form method="POST"
                                                    action="{{ route('order.retry-roam-api', ['roamOrder' => $order->id]) }}"
                                                    onsubmit="return confirm('Retry the failed Roam API call for this paid order?');">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-warning order-action-btn order-action-btn-solid">
                                                        <i class="ti ti-refresh"></i> Retry Roam API
                                                    </button>
                                                </form>
                                            @endif

                                            @if (!str_starts_with((string) $order->roam_order_num, 'TMP-'))
                                                <form method="POST"
                                                    action="{{ route('roam.orders.sync', ['roamOrder' => $order->id]) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-secondary order-action-btn order-action-btn-solid">
                                                        <i class="ti ti-refresh"></i> Sync
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($showRoamRefundAction)
                                                @if ($canRoamRefund)
                                                    <form method="POST"
                                                        action="{{ route('order.refund', ['roamOrder' => $order->id]) }}"
                                                        onsubmit="return confirm('Request Roam refund API for this order?');">
                                                        @csrf
                                                        <input type="hidden" name="refund_method"
                                                            value="{{ \App\Models\RoamOrder::REFUND_METHOD_ROAM_API }}">
                                                        <button type="submit"
                                                            class="btn order-action-btn order-action-btn-solid order-action-btn-refund">
                                                            <i class="ti ti-receipt-refund"></i> Roam Portal Refund
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if (str_starts_with((string) $order->roam_order_num, 'TMP-') && !$showRoamRefundAction && !$canRetryThisOrder)
                                                <div class="text-muted small">No Roam portal actions available.</div>
                                            @elseif (
                                                !str_starts_with((string) $order->roam_order_num, 'TMP-') &&
                                                    !$showRoamRefundAction &&
                                                    !$canRoamRefund &&
                                                    !$canRetryThisOrder)
                                                @if (!$isRoamApiSuccess)
                                                    <div class="text-muted small">No Roam portal actions available.</div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
