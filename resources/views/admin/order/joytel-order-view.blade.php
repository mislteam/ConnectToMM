@extends('admin.layouts.index')
@section('title', 'Joytel Order Details')
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

        .order-detail-shell {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1fr) 320px;
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

        .order-tag {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 0.78rem;
            font-weight: 700;
            gap: 0.35rem;
            padding: 0.3rem 0.72rem;
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

        .order-item-table td,
        .order-item-table th {
            vertical-align: top;
        }

        .order-item-name {
            color: #111827;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .order-item-subtext,
        .order-action-caption {
            color: #6b7280;
            font-size: 0.70rem;
            margin-top: 0.35rem;
        }

        .order-item-list {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
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

        .order-action-order-num {
            font-size: 0.78rem !important;
            font-weight: 560 !important;
            margin-bottom: 0.55rem;
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
            $primaryOrder && (int) $primaryOrder->our_status === \App\Models\JoytelOrder::OUR_STATUS_PENDING_PAYMENT;
        $canAdminCancel = $canApprovePayment;
        $statusTagClass = match ($summary['status_key'] ?? null) {
            'completed' => 'order-tag-success',
            'pending_payment' => 'order-tag-warning',
            'failed', 'cancelled', 'admin_cancelled' => 'order-tag-danger',
            'refunded' => 'order-tag-info',
            default => 'order-tag-primary',
        };
        $paymentTagClass = match ($summary['payment_label'] ?? null) {
            'Paid' => 'order-tag-success',
            'Pending' => 'order-tag-warning',
            'Cancelled' => 'order-tag-danger',
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
                    <li class="breadcrumb-item">
                        <a href="{{ route('order.joytel') }}" class="order-detail-breadcrumb-link">Orders</a>
                    </li>
                    <li class="breadcrumb-item active order-detail-breadcrumb-current">{{ $summary['reference'] }}</li>
                </ol>
            </div>
            <div class="py-3">
                <a href="{{ route('order.joytel') }}" class="back-to-list-link">Back</a>
            </div>
        </div>

        <div class="order-detail-shell">
            <div>
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
                                        {{ optional($summary['created_at'])->format('Y-m-d') ?? '-' }}</div>
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
                                    <div class="order-meta-value"><a
                                            href="mailto:{{ $summary['customer_email'] }}">{{ $summary['customer_email'] }}</a>
                                    </div>
                                </div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Phone</span>
                                    <div class="order-meta-value">{{ $summary['customer_phone'] ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="order-meta-panel">
                                <div class="order-meta-heading">Order Summary</div>
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Products</span>
                                    @php
                                        // dd($summary['product_summary']);
                                    @endphp
                                    <div class="order-meta-value">
                                        @forelse ($summary['product_names'] as $product)
                                            {{-- {!! nl2br(e($product['name'] ?: '-')) !!} --}}
                                            {{ $product['name'] }}
                                            <div class="order-item-subtext text-dark">{{ $product['meta'] }}</div>
                                        @empty
                                            -
                                        @endforelse
                                    </div>
                                </div>
                                {{-- <div class="order-meta-row">
                                    <span class="order-meta-label">Payment</span>
                                    <div class="order-meta-value">
                                        <span class="order-tag {{ $paymentTagClass }}">
                                            <i class="ti ti-credit-card"></i> {{ $summary['payment_label'] }}
                                        </span>
                                    </div>
                                </div> --}}
                                <div class="order-meta-row">
                                    <span class="order-meta-label">Order total</span>
                                    <div class="order-meta-value fw-semibold">
                                        {{ number_format((float) $summary['amount']) }} MMK
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
                                    <th>Joytel Order No</th>
                                    <th>Product</th>
                                    <th>Service</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        $isAdminCancelled =
                                            (int) $order->our_status ===
                                            \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED;
                                        $statusLabel = $isAdminCancelled
                                            ? 'Admin Cancel'
                                            : \App\Models\JoytelOrder::CUSTOMER_STATUS_LABELS[
                                                    (int) $order->our_status
                                                ] ?? (string) $order->our_status;
                                        $itemStatusClass = match ((int) $order->our_status) {
                                            \App\Models\JoytelOrder::OUR_STATUS_COMPLETED => 'order-tag-success',
                                            \App\Models\JoytelOrder::OUR_STATUS_PENDING_PAYMENT => 'order-tag-warning',
                                            \App\Models\JoytelOrder::OUR_STATUS_API_FAILED,
                                            \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED,
                                            \App\Models\JoytelOrder::OUR_STATUS_CANCELLED
                                                => 'order-tag-danger',
                                            \App\Models\JoytelOrder::OUR_STATUS_REFUNDED => 'order-tag-info',
                                            default => 'order-tag-primary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="order-item-name">{{ $order->joytel_order_num }}</div>
                                            <div class="order-item-subtext">Qty: {{ max(1, (int) $order->quantity) }}</div>
                                            {{-- <div class="order-item-subtext">Outer: {{ $order->outer_order_id ?: '-' }}</div> --}}
                                        </td>
                                        <td>
                                            <div class="order-item-name">
                                                {{ $order->formatted_product_name['name'] ?: '-' }}
                                            </div>
                                            <div class="order-item-subtext">
                                                {{ $order->formatted_product_name['meta'] }}
                                            </div>

                                        </td>
                                        <td>
                                            <div class="order-item-name">
                                                {{ \Illuminate\Support\Str::headline((string) $order->service_type) }}
                                            </div>
                                            <div class="order-item-subtext">
                                                {{ \Illuminate\Support\Str::headline((string) $order->order_type) }}
                                            </div>
                                            {{-- @if ($order->source_sn_code)
                                                <div class="order-item-subtext">Source SN: {{ $order->source_sn_code }}
                                                </div>
                                            @endif --}}
                                        </td>
                                        <td>
                                            @if ($order->items->isEmpty())
                                                <span class="text-muted">-</span>
                                            @else
                                                <div class="order-item-list">
                                                    @foreach ($order->items as $item)
                                                        <span>
                                                            {{ $item->product_code ?: 'Product' }}
                                                            @if ($item->sn_code)
                                                                / SN: {{ $item->sn_code }}
                                                            @endif
                                                        </span>
                                                        @if ($item->sn_pin)
                                                            <span class="text-muted">SN PIN: {{ $item->sn_pin }}</span>
                                                        @endif
                                                        @if ($item->cid)
                                                            <span class="text-muted">CID: {{ $item->cid }}</span>
                                                        @endif
                                                        @if ($item->pin1 || $item->pin2)
                                                            <span class="text-muted">PIN:
                                                                {{ $item->pin1 ?: '-' }} / {{ $item->pin2 ?: '-' }}</span>
                                                        @endif
                                                        @if ($item->puk1 || $item->puk2)
                                                            <span class="text-muted">PUK:
                                                                {{ $item->puk1 ?: '-' }} / {{ $item->puk2 ?: '-' }}</span>
                                                        @endif
                                                        @if ($item->qrcode)
                                                            <span class="text-muted">QR:
                                                                {{ \Illuminate\Support\Str::limit($item->qrcode, 48) }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="order-item-name">
                                                {{ number_format((float) $order->billable_total_price) }} MMK</div>
                                            {{-- <div class="order-item-subtext">
                                                {{ number_format((float) $order->unit_price) }} MMK x
                                                {{ max(1, (int) $order->quantity) }}
                                            </div> --}}
                                        </td>
                                        <td>
                                            <span class="order-tag {{ $itemStatusClass }}">
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

            <aside>
                <section class="order-detail-card">
                    <div class="order-detail-card-header">
                        <h5 class="mb-0">Order Actions</h5>
                    </div>
                    <div class="order-detail-card-body">
                        <div class="order-actions-stack">
                            <div class="order-action-block">
                                <h6 class="order-action-section-title">User Actions</h6>
                                {{-- <div class="order-action-caption">
                                    Payment Method:
                                    {{ \Illuminate\Support\Str::headline((string) ($primaryOrder?->payment_method ?: 'bank_transfer')) }}
                                </div> --}}
                                <div class="order-action-buttons">
                                    @if ($paymentSlipUrl)
                                        <a href="{{ $paymentSlipUrl }}" target="_blank" rel="noopener"
                                            class="btn btn-secondary order-action-btn order-action-btn-solid">
                                            <i class="ti ti-photo"></i> View Slip
                                        </a>
                                    @endif

                                    {{-- @if ($canApprovePayment)
                                        <form method="POST"
                                            action="{{ route('order.joytel.approve-payment', ['joytelOrder' => $primaryOrder->id]) }}"
                                            onsubmit="return confirm('Approve payment for this Joytel order reference?');">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-success order-action-btn order-action-btn-solid">
                                                <i class="ti ti-circle-check"></i> Approve Payment
                                            </button>
                                        </form>
                                    @endif

                                    @if ($canAdminCancel)
                                        <form method="POST"
                                            action="{{ route('order.joytel.cancel-payment', ['joytelOrder' => $primaryOrder->id]) }}"
                                            onsubmit="return confirm('Cancel this Joytel order by admin?');">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-danger order-action-btn order-action-btn-solid">
                                                <i class="ti ti-circle-x"></i> Admin Cancel
                                            </button>
                                        </form>
                                    @endif --}}

                                    @if ($canApprovePayment || $canAdminCancel)
                                        <div class="payment-action-dropdown dropdown w-100">
                                            <button
                                                class="btn btn-success dropdown-toggle order-action-btn order-action-btn-solid w-100"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{-- <i class="ti ti-settings"></i> --}}
                                                Payment Actions
                                            </button>

                                            <ul class="dropdown-menu payment-action-menu w-100">
                                                @if ($canApprovePayment)
                                                    <li>
                                                        <form method="POST"
                                                            action="{{ route('order.joytel.approve-payment', ['joytelOrder' => $primaryOrder->id]) }}"
                                                            onsubmit="return confirm('Approve payment for this Joytel order reference?');">
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
                                                            action="{{ route('order.joytel.cancel-payment', ['joytelOrder' => $primaryOrder->id]) }}"
                                                            onsubmit="return confirm('Cancel this Joytel order by admin?');">
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
                                        <div class="text-muted small">No user actions available.</div>
                                    @endif
                                </div>

                                @foreach ($orders as $order)
                                    @php
                                        $isAdminCancelled =
                                            (int) $order->our_status ===
                                            \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED;
                                        $actionStatusLabel = $isAdminCancelled
                                            ? 'Admin Cancel'
                                            : \App\Models\JoytelOrder::CUSTOMER_STATUS_LABELS[
                                                    (int) $order->our_status
                                                ] ?? (string) $order->our_status;
                                        $actionStatusTagClass = match ((int) $order->our_status) {
                                            \App\Models\JoytelOrder::OUR_STATUS_COMPLETED => 'order-tag-success',
                                            \App\Models\JoytelOrder::OUR_STATUS_PENDING_PAYMENT => 'order-tag-warning',
                                            \App\Models\JoytelOrder::OUR_STATUS_API_FAILED,
                                            \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED,
                                            \App\Models\JoytelOrder::OUR_STATUS_CANCELLED
                                                => 'order-tag-danger',
                                            \App\Models\JoytelOrder::OUR_STATUS_REFUNDED => 'order-tag-info',
                                            default => 'order-tag-primary',
                                        };
                                    @endphp

                                    <div class="order-action-block order-action-item">
                                        <h6 class="order-action-order-num">{{ $order->joytel_order_num }}</h6>
                                        <div class="order-action-status">
                                            <span class="order-tag {{ $actionStatusTagClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $actionStatusLabel }}
                                            </span>
                                        </div>
                                        <div class="order-action-buttons">
                                            <div class="text-muted small">No user actions available.</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="order-action-block">
                                <h6 class="order-action-section-title">Joytel Portal Actions</h6>

                                @foreach ($orders as $order)
                                    @php
                                        $isAdminCancelled =
                                            (int) $order->our_status ===
                                            \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED;
                                        $portalStatusLabel = $isAdminCancelled
                                            ? 'Admin Cancel'
                                            : \App\Models\JoytelOrder::CUSTOMER_STATUS_LABELS[
                                                    (int) $order->our_status
                                                ] ?? (string) $order->our_status;
                                        $portalStatusTagClass = match ((int) $order->our_status) {
                                            \App\Models\JoytelOrder::OUR_STATUS_COMPLETED => 'order-tag-success',
                                            \App\Models\JoytelOrder::OUR_STATUS_PENDING_PAYMENT => 'order-tag-warning',
                                            \App\Models\JoytelOrder::OUR_STATUS_API_FAILED,
                                            \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED,
                                            \App\Models\JoytelOrder::OUR_STATUS_CANCELLED
                                                => 'order-tag-danger',
                                            \App\Models\JoytelOrder::OUR_STATUS_REFUNDED => 'order-tag-info',
                                            default => 'order-tag-primary',
                                        };
                                        $canRetryJoytelApi =
                                            str_starts_with((string) $order->joytel_order_num, 'JTMP-') &&
                                            in_array(
                                                (int) $order->our_status,
                                                [
                                                    \App\Models\JoytelOrder::OUR_STATUS_PAID,
                                                    \App\Models\JoytelOrder::OUR_STATUS_API_PROCESSING,
                                                    \App\Models\JoytelOrder::OUR_STATUS_API_FAILED,
                                                ],
                                                true,
                                            );
                                        $canSyncJoytelItems =
                                            !str_starts_with((string) $order->joytel_order_num, 'JTMP-') &&
                                            ((int) $order->our_status ===
                                                \App\Models\JoytelOrder::OUR_STATUS_API_PROCESSING ||
                                                $order->items->contains(
                                                    fn($item) => empty($item->sn_code) ||
                                                        (strtolower((string) $order->service_type) === 'esim' &&
                                                            (empty($item->sn_pin) ||
                                                                empty($item->cid) ||
                                                                empty($item->qrcode) ||
                                                                $item->pin1 === null ||
                                                                $item->pin2 === null ||
                                                                $item->puk1 === null ||
                                                                $item->puk2 === null)),
                                                ));
                                    @endphp

                                    <div class="order-action-block order-action-item">
                                        <h6 class="order-action-order-num">{{ $order->joytel_order_num }}</h6>
                                        <div class="order-action-status">
                                            <span class="order-tag {{ $portalStatusTagClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $portalStatusLabel }}
                                            </span>
                                        </div>
                                        <div class="order-action-buttons">
                                            @if ($canRetryJoytelApi)
                                                <form method="POST"
                                                    action="{{ route('order.joytel.retry-api', ['joytelOrder' => $order->id]) }}"
                                                    onsubmit="return confirm('Submit this order to Joytel API again?');">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-warning order-action-btn order-action-btn-solid">
                                                        <i class="ti ti-refresh"></i> Submit Joytel API
                                                    </button>
                                                </form>
                                            @elseif ($canSyncJoytelItems)
                                                <form method="POST"
                                                    action="{{ route('order.joytel.sync-items', ['joytelOrder' => $order->id]) }}"
                                                    onsubmit="return confirm('Sync delivery status and eSIM details from Joytel?');">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-secondary order-action-btn order-action-btn-solid">
                                                        <i class="ti ti-refresh"></i> Sync Joytel eSIM
                                                    </button>
                                                </form>
                                            @else
                                                <div class="text-muted small">No Joytel portal actions available.</div>
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
