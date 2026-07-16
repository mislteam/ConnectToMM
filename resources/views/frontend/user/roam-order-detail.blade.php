@extends('frontend.layouts.index')
@section('title', \Illuminate\Support\Str::headline($provider ?? 'Roam') . ' Order Detail')

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

        .sim-details-row.has-check {
            grid-template-columns: minmax(0, 1fr) 120px 100px;
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

        .joytel-usage-result {
            border: 1px solid #e7ebf3;
            border-radius: 8px;
            background: #f9fbff;
            padding: 12px;
        }

        .joytel-usage-result table {
            margin-bottom: 0;
        }

        .joytel-usage-result th,
        .joytel-usage-result td {
            font-size: 13px;
            vertical-align: top;
            word-break: break-word;
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

            .sim-details-row.has-check {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
    <x-banner key="order_detail" />

    <section class="py-5">
        <div class="container">
            <div class="col-xl-12">
                <div class="order-detail-toolbar">
                    <div class="order-actions">
                        @if (!empty($can_pay) && !empty($outer_order_id) && !empty($payment_route))
                            <a href="{{ $payment_route }}" class="btn-upload">
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
                                @php
                                    $providerName = strtolower((string) ($provider ?? 'roam'));
                                    $providerOrderNo =
                                        $providerName === 'joytel'
                                            ? $order->joytel_order_num ?? '-'
                                            : $order->roam_order_num ?? '-';
                                    $productName =
                                        $order->formatted_product_name ??
                                        ($order->remark ?? ($order->sku_id ?? ($order->product_code ?? '-')));
                                    $amount = $order->unit_price ?? $order->billable_total_price;
                                    $pendingPaymentStatus =
                                        $providerName === 'joytel'
                                            ? \App\Models\JoytelOrder::OUR_STATUS_PENDING_PAYMENT
                                            : \App\Models\RoamOrder::OUR_STATUS_PENDING_PAYMENT;
                                    $processingStatus =
                                        $providerName === 'joytel'
                                            ? \App\Models\JoytelOrder::OUR_STATUS_API_PROCESSING
                                            : \App\Models\RoamOrder::OUR_STATUS_API_PROCESSING;
                                    $refundedStatus =
                                        $providerName === 'joytel'
                                            ? \App\Models\JoytelOrder::OUR_STATUS_REFUNDED
                                            : \App\Models\RoamOrder::OUR_STATUS_REFUNDED;
                                    $adminCancelledStatus =
                                        $providerName === 'joytel'
                                            ? \App\Models\JoytelOrder::OUR_STATUS_ADMIN_CANCELLED
                                            : \App\Models\RoamOrder::OUR_STATUS_ADMIN_CANCELLED;
                                @endphp
                                <div class="order-section">
                                    <div class="order-section-title">Order Details</div>
                                    <div class="detail-grid">
                                        <div class="detail-item">
                                            <div class="detail-label">{{ $provider_order_no_label ?? 'Order No' }}</div>
                                            <div class="detail-value">{{ $providerOrderNo }}</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Package</div>
                                            <div class="detail-value">
                                                {{ $productName }}
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Amount</div>
                                            <div class="detail-value">
                                                {{ number_format((float) $amount) }}
                                                MMK
                                            </div>
                                        </div>
                                        @if ($providerName === 'roam' && (int) $order->our_status === $refundedStatus)
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
                                                {{ \Illuminate\Support\Str::headline($order->service_type ?? '-') }}</div>
                                        </div>
                                    </div>

                                    @if ($order->items && $order->items->isNotEmpty())
                                        <div class="mt-4">
                                            <h5 class="mb-3">SIM Details</h5>
                                            <div class="sim-details-list">
                                                <div
                                                    class="sim-details-row is-head {{ $providerName === 'joytel' ? 'has-check' : '' }}">
                                                    <div>{{ $providerName === 'joytel' ? 'Code / QR' : 'ICCID' }}</div>

                                                    @if ($providerName === 'joytel')
                                                        <div class="sim-details-pdf">Check</div>
                                                    @endif
                                                </div>
                                                @foreach ($order->items as $item)
                                                    @php
                                                        $joytelItemPayload = [];
                                                        if ($providerName === 'joytel') {
                                                            $joytelItemPayload[] = [
                                                                'service_type' => strtolower(
                                                                    (string) $order->service_type,
                                                                ),
                                                                'product_name' =>
                                                                    $order->product_name ?:
                                                                    $order->remark ?:
                                                                    $item->product_code,
                                                                'product_code' => $item->product_code,
                                                                'sn_pin' => $item->sn_pin,
                                                                'cid' => $item->cid ?: $item->sn_code,
                                                                'sn_code' => $item->sn_code,
                                                                'rsp_order_id' => data_get(
                                                                    $item->raw_callback_data,
                                                                    'joytel_query_order.sn.rspOrderId',
                                                                ),
                                                            ];
                                                        }
                                                    @endphp
                                                    <div
                                                        class="sim-details-row {{ $providerName === 'joytel' ? 'has-check' : '' }}">
                                                        <div class="sim-details-value">
                                                            @if ($providerName === 'joytel')
                                                                <div>
                                                                    {{ $item->sn_code ?? ($item->cid ?? ($item->qrcode ?? 'Waiting for provisioning')) }}
                                                                </div>
                                                                @if ($item->sn_pin)
                                                                    <div class="text-muted small">SN PIN:
                                                                        {{ $item->sn_pin }}</div>
                                                                @endif
                                                                @if (data_get($item->raw_callback_data, 'joytel_query_order.sn.rspOrderId'))
                                                                    <div class="text-muted small">RSP Order ID:
                                                                        {{ data_get($item->raw_callback_data, 'joytel_query_order.sn.rspOrderId') }}
                                                                    </div>
                                                                @endif
                                                            @else
                                                                {{ $item->iccid }}
                                                            @endif
                                                        </div>
                                                        {{-- <div class="sim-details-pdf">
                                                            @if ($providerName === 'joytel')
                                                                {{ (int) ($item->status ?? 0) === 1 ? 'Success' : 'Pending' }}
                                                            @elseif (!empty($item->pdf_url))
                                                                <a href="{{ $item->pdf_url }}" target="_blank"
                                                                    class="pdf-link">Open</a>
                                                            @else
                                                                -
                                                            @endif
                                                        </div> --}}
                                                        @if ($providerName === 'joytel')
                                                            <div class="sim-details-pdf">
                                                                <button type="button"
                                                                    class="btn btn-primary btn-sm joytel-usage-check-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#joytelUsageModal" data-manual="0"
                                                                    data-outer-order-id="{{ $outer_order_id }}"
                                                                    data-service-type="{{ strtolower((string) $order->service_type) }}"
                                                                    data-sn-pin="{{ $item->sn_pin }}"
                                                                    data-cid="{{ $item->cid ?: $item->sn_code }}"
                                                                    data-sn-code="{{ $item->sn_code }}"
                                                                    data-rsp-order-id="{{ data_get($item->raw_callback_data, 'joytel_query_order.sn.rspOrderId') }}"
                                                                    data-items="{{ e(json_encode($joytelItemPayload)) }}">
                                                                    Check
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 text-muted">
                                            @if ((int) $order->our_status === $pendingPaymentStatus)
                                                Waiting for payment.
                                            @elseif ((int) $order->our_status === $processingStatus)
                                                Creating your eSIM. Please refresh in a moment.
                                            @elseif ((int) $order->our_status === $adminCancelledStatus)
                                                This order was cancelled by admin.
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

    @if (($provider ?? '') === 'joytel')
        <div class="modal fade" id="joytelUsageModal" tabindex="-1" aria-labelledby="joytelUsageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="joytelUsageForm">
                    @csrf
                    <input type="hidden" name="outer_order_id" id="joytel_usage_outer_order_id">
                    <input type="hidden" name="service_type" id="joytel_usage_service_type">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="joytelUsageModalLabel">Check Joytel Usage</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger d-none" id="joytelUsageError"></div>
                            <div class="alert alert-success d-none" id="joytelUsageSuccess"></div>

                            <div id="joytelUsageEsimFields">
                                <div class="mb-3">
                                    <label for="joytel_usage_sn_pin" class="form-label">SN PIN <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="sn_pin" id="joytel_usage_sn_pin"
                                        placeholder="Enter SN PIN" readonly>
                                </div>
                            </div>

                            <div id="joytelUsageRechargeFields" class="d-none">
                                <div class="mb-3">
                                    <label for="joytel_usage_cid" class="form-label">SN Code / CID <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="cid" id="joytel_usage_cid"
                                        placeholder="Enter SN Code or CID" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="joytel_usage_rsp_order_id" class="form-label">RSP Order ID <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="rsp_order_id"
                                        id="joytel_usage_rsp_order_id" placeholder="Enter RSP Order ID" readonly>
                                </div>
                            </div>

                            <div id="joytelUsageResult" class="joytel-usage-result d-none"></div>
                        </div>
                        <div class="modal-footer">
                            {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                            <button type="submit" class="btn btn-primary" id="joytelUsageSubmit">Check Usage</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var joytelUsageForm = document.getElementById('joytelUsageForm');
                var joytelUsageError = document.getElementById('joytelUsageError');
                var joytelUsageSuccess = document.getElementById('joytelUsageSuccess');
                var joytelUsageResult = document.getElementById('joytelUsageResult');
                var joytelUsageSubmit = document.getElementById('joytelUsageSubmit');
                var joytelUsageEsimFields = document.getElementById('joytelUsageEsimFields');
                var joytelUsageRechargeFields = document.getElementById('joytelUsageRechargeFields');

                function clearJoytelUsageAlerts() {
                    joytelUsageError.classList.add('d-none');
                    joytelUsageSuccess.classList.add('d-none');
                    joytelUsageResult.classList.add('d-none');
                    joytelUsageError.textContent = '';
                    joytelUsageSuccess.textContent = '';
                    joytelUsageResult.innerHTML = '';
                }

                function setJoytelUsageMode(serviceType) {
                    var mode = (serviceType || 'esim').toLowerCase();
                    var isRecharge = mode === 'physical';

                    document.getElementById('joytel_usage_service_type').value = isRecharge ? 'physical' : 'esim';
                    joytelUsageEsimFields.classList.toggle('d-none', isRecharge);
                    joytelUsageRechargeFields.classList.toggle('d-none', !isRecharge);
                }

                function fillJoytelUsageFields(item) {
                    item = item || {};
                    setJoytelUsageMode(item.service_type || 'esim');
                    document.getElementById('joytel_usage_sn_pin').value = item.sn_pin || '';
                    document.getElementById('joytel_usage_cid').value = item.cid || item.sn_code || '';
                    document.getElementById('joytel_usage_rsp_order_id').value = item.rsp_order_id || '';
                }

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function humanizeKey(key) {
                    return String(key)
                        .replace(/_/g, ' ')
                        .replace(/([a-z])([A-Z])/g, '$1 $2')
                        .replace(/\b\w/g, function(char) {
                            return char.toUpperCase();
                        });
                }

                function renderJoytelUsageValue(value) {
                    if (Array.isArray(value)) {
                        if (value.length === 0) {
                            return '<span class="text-muted">-</span>';
                        }

                        return value.map(function(item, index) {
                            return '<div class="mb-2"><strong>Item ' + (index + 1) + '</strong>' +
                                renderJoytelUsageValue(item) + '</div>';
                        }).join('');
                    }

                    if (value && typeof value === 'object') {
                        if (value.__joytelUsageSummary) {
                            var mainRows = (value.rows || []).map(function(row) {
                                return '<tr><td>' + escapeHtml(row.label || '-') +
                                    '</td><td class="text-right">' + escapeHtml(row.value || '-') +
                                    '</td></tr>';
                            }).join('');
                            var usageRecords = value.usage_records || [];
                            var usageHtml = '';

                            if (usageRecords.length > 0) {
                                usageHtml = '<div class="mt-3"><strong>Daily Usage</strong>' +
                                    usageRecords.map(function(record, index) {
                                        return '<div class="mt-2"><strong>Item ' + (index + 1) +
                                            '</strong>' + renderJoytelUsageValue(record) + '</div>';
                                    }).join('') + '</div>';
                            }

                            return '<div class="table-responsive"><table class="table table-sm table-bordered">' +
                                '<tbody><tr class="table-info"><th colspan="2">' +
                                escapeHtml(value.title || 'Total Usage') +
                                '</th></tr>' + mainRows + '</tbody></table></div>' + usageHtml;
                        }

                        var rows = Object.keys(value).map(function(key) {
                            return '<tr><th style="width: 34%;">' + escapeHtml(humanizeKey(key)) +
                                '</th><td>' + renderJoytelUsageValue(value[key]) + '</td></tr>';
                        }).join('');

                        return '<div class="table-responsive"><table class="table table-sm table-bordered">' +
                            '<tbody>' + rows + '</tbody></table></div>';
                    }

                    return value === null || value === '' || typeof value === 'undefined' ?
                        '<span class="text-muted">-</span>' :
                        escapeHtml(value);
                }

                document.querySelectorAll('.joytel-usage-check-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        clearJoytelUsageAlerts();
                        document.getElementById('joytel_usage_outer_order_id').value = button
                            .getAttribute(
                                'data-outer-order-id') || '';

                        var items = [];
                        var directItem = {
                            service_type: button.getAttribute('data-service-type') || 'esim',
                            sn_pin: button.getAttribute('data-sn-pin') || '',
                            cid: button.getAttribute('data-cid') || '',
                            sn_code: button.getAttribute('data-sn-code') || '',
                            rsp_order_id: button.getAttribute('data-rsp-order-id') || ''
                        };

                        try {
                            items = JSON.parse(button.getAttribute('data-items') || '[]');
                        } catch (error) {
                            items = [];
                        }

                        fillJoytelUsageFields(directItem.sn_pin || directItem.cid || directItem
                            .rsp_order_id ?
                            directItem :
                            (items[0] || {
                                service_type: button.getAttribute('data-service-type') || 'esim'
                            }));
                    });
                });

                if (joytelUsageForm) {
                    joytelUsageForm.addEventListener('submit', function(event) {
                        event.preventDefault();
                        clearJoytelUsageAlerts();
                        joytelUsageSubmit.disabled = true;
                        joytelUsageSubmit.textContent = 'Checking...';

                        fetch("{{ route('customer.joytel.usage.check') }}", {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                },
                                body: new FormData(joytelUsageForm)
                            })
                            .then(function(response) {
                                return response.json().then(function(data) {
                                    return {
                                        ok: response.ok,
                                        data: data
                                    };
                                });
                            })
                            .then(function(result) {
                                if (!result.ok || !result.data.ok) {
                                    joytelUsageError.textContent = result.data.message ||
                                        'Usage check failed.';
                                    joytelUsageError.classList.remove('d-none');
                                    return;
                                }

                                joytelUsageSuccess.textContent = result.data.message ||
                                    'Usage check success.';
                                joytelUsageSuccess.classList.remove('d-none');
                                joytelUsageResult.innerHTML = renderJoytelUsageValue(result.data.summary ||
                                    result
                                    .data.raw || {});
                                joytelUsageResult.classList.remove('d-none');
                            })
                            .catch(function(error) {
                                joytelUsageError.textContent = error.message || 'Usage check failed.';
                                joytelUsageError.classList.remove('d-none');
                            })
                            .finally(function() {
                                joytelUsageSubmit.disabled = false;
                                joytelUsageSubmit.textContent = 'Check Usage';
                            });
                    });
                }
            });
        </script>
    @endif
@endsection
