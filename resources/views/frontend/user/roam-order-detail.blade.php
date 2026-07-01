@extends('frontend.layouts.index')
@section('title', 'Roam Order Detail')

@section('content')
    @include('components.alert')
    <style>
        .order-detail-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin: 20px 0 15px;
        }

        .order-card {
            border: 1px solid #dee2e6;
            background: #fff;
            margin-bottom: 30px;
        }

        .order-card-header {
            background: #fff;
            color: #004aad;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            border-bottom: 1px solid #dee2e6;
        }

        .order-id {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            color: #212529;
        }

        .order-label {
            color: #004aad;
        }

        .payment-badge {
            background: transparent;
            color: #004aad;
            padding: 0;
            border-radius: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .order-card-body {
            padding: 15px;
        }

        .order-section {
            border-top: 2px solid #cfd8e3;
            padding-top: 22px;
            margin-top: 24px;
        }

        .order-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #004aad;
            margin-bottom: 15px;
        }

        .order-section+.order-section {
            border-top-color: #b8c7da;
        }

        .summary-lines {
            padding: 15px 0 5px;
        }

        .summary-line {
            display: flex;
            align-items: baseline;
            gap: 10px;
            padding: 6px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .summary-line:last-child {
            border-bottom: none;
        }

        .summary-line-label {
            min-width: 140px;
            color: #212529;
            font-size: 14px;
        }

        .summary-line-value {
            font-size: 16px;
            font-weight: 700;
            color: #5f6770;
            word-break: break-word;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
        }

        .detail-label {
            color: #5f6770;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 700;
            word-break: break-word;
        }

        .order-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-renew,
        .btn-upload {
            border-radius: 0;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-renew {
            background: #004aad;
            border: 1px solid #004aad;
            color: #fff;
        }

        .btn-upload {
            background: #004aad;
            border: 1px solid #004aad;
            color: #fff;
        }

        .btn-renew:hover {
            background: #003b87;
            border-color: #003b87;
            color: #fff;
        }

        .btn-upload:hover {
            background: #003b87;
            border-color: #003b87;
            color: #fff;
        }

        .btn-renew i {
            color: inherit;
        }

        .section-label {
            color: #5f6770;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .order-card .text-muted {
            color: #5f6770 !important;
        }

        .pdf-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 82px;
            padding: 7px 14px;
            border: 1px solid #004aad;
            color: #004aad;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.2;
            background: #fff;
        }

        .pdf-link:hover {
            background: #004aad;
            color: #fff;
            text-decoration: none;
        }

        .sim-details-list {
            max-width: 520px;
            border-top: 1px solid #dee2e6;
        }

        .sim-details-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 120px;
            gap: 24px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .sim-details-row.is-head {
            color: #212529;
            font-size: 14px;
            font-weight: 700;
        }

        .sim-details-value {
            font-weight: 500;
            word-break: break-word;
        }

        .sim-details-pdf {
            text-align: center;
        }

        @media(max-width:768px) {
            .order-detail-toolbar {
                justify-content: flex-start;
                margin-top: 20px;
            }

            .summary-line {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }

            .summary-line-label {
                min-width: 0;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .order-card-header {
                align-items: flex-start;
            }

            .sim-details-row {
                grid-template-columns: minmax(0, 1fr) 96px;
                gap: 12px;
            }
        }
    </style>
    <x-banner key="order_detail" />

    <section class="py-5">
        <div class="container">
            <div class="col-xl-12">
                <div class="order-detail-toolbar">
                    <div class="order-actions">
                        @if (!empty($can_pay) && !empty($outer_order_id))
                            <a href="{{ route('roam.payment.show', ['outerOrderId' => $outer_order_id]) }}"
                                class="btn-upload">
                                <i class="fa fa-credit-card"></i> Pay Now
                            </a>
                        @endif
                        <a href="{{ route('customer.profile.index') }}" class="btn-renew">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-id">
                            <span class="order-label">Order ID :</span>
                            {{ $outer_order_id ?? '-' }}
                        </div>
                        <div class="payment-badge">
                            <span class="{{ $status_class ?? '' }}">{{ $status_label ?? 'Processing' }}</span>
                        </div>
                    </div>

                    <div class="order-card-body">
                        <div class="summary-lines">
                            <div class="summary-line">
                                <div class="summary-line-label">Date</div>
                                <div class="summary-line-value">
                                    @if (!empty($created_at))
                                        {{ \Carbon\Carbon::parse($created_at)->format('Y-m-d') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="summary-line">
                                <div class="summary-line-label">Total Amount</div>
                                <div class="summary-line-value">{{ number_format((float) ($total ?? 0)) }} MMK</div>
                            </div>
                            <div class="summary-line">
                                <div class="summary-line-label">Orders</div>
                                <div class="summary-line-value">{{ isset($orders) ? $orders->count() : 0 }}</div>
                            </div>

                            <div class="summary-line">
                                <div class="summary-line-label">Payment Method</div>
                                <div class="summary-line-value">
                                    {{ $payment_method ? ucwords(str_replace('_', ' ', $payment_method)) : '-' }}
                                </div>
                            </div>
                        </div>
                        @if (!empty($orders))
                            @foreach ($orders as $order)
                                <div class="order-section">
                                    <div class="order-section-title">Order Details</div>
                                    <div class="detail-grid">
                                        <div class="detail-item">
                                            <div class="detail-label">Roam Order No</div>
                                            <div class="detail-value">{{ $order->roam_order_num ?? '-' }}</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Package</div>
                                            <div class="detail-value">
                                                {{ $order->formatted_product_name ?? ($order->remark ?? ($order->sku_id ?? '-')) }}
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Amount</div>
                                            <div class="detail-value">
                                                {{ number_format((float) ($order->unit_price ?? $order->billable_total_price)) }}
                                                MMK
                                            </div>
                                        </div>
                                        @if ((int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_REFUNDED)
                                            <div class="detail-item">
                                                <div class="detail-label">Refund</div>
                                                <div class="detail-value">
                                                    {{ $order->refund_method_label }}
                                                    @if ($order->refund_amount !== null)
                                                        - {{ number_format((float) $order->refund_amount) }} MMK
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        <div class="detail-item">
                                            <div class="detail-label">Qty</div>
                                            <div class="detail-value">{{ (int) $order->quantity }}</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Service Type</div>
                                            <div class="detail-value">
                                                {{ \Illuminate\Support\Str::headline($order->service_type) }}</div>
                                        </div>
                                    </div>

                                    @if ($order->items && $order->items->isNotEmpty())
                                        <div class="mt-4">
                                            <h5 class="mb-3">SIM Details</h5>
                                            <div class="sim-details-list">
                                                <div class="sim-details-row is-head">
                                                    <div>ICCID</div>
                                                    <div class="sim-details-pdf">PDF</div>
                                                </div>
                                                @foreach ($order->items as $item)
                                                    <div class="sim-details-row">
                                                        <div class="sim-details-value">{{ $item->iccid }}</div>
                                                        <div class="sim-details-pdf">
                                                            @if (!empty($item->pdf_url))
                                                                <a href="{{ $item->pdf_url }}" target="_blank"
                                                                    class="pdf-link">Open</a>
                                                            @else
                                                                -
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 text-muted">
                                            @if ((int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_PENDING_PAYMENT)
                                                Waiting for payment.
                                            @elseif ((int) $order->our_status === \App\Models\RoamOrder::OUR_STATUS_API_PROCESSING)
                                                Creating your eSIM. Please refresh in a moment.
                                            @else
                                                Details will appear here once the eSIM is created.
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </section>
@endsection
