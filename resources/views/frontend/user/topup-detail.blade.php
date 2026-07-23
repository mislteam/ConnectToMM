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
    <x-banner key="topup_payment_detail" />
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

        $isApproved = strtolower($transaction->transaction_state ?? 'pending') == 'approved';
    @endphp
    <section class="py-5">
        <div class="container">
            <div class="col-xl-12">
                <div class="order-detail-toolbar">
                    <div class="order-actions">
                        @if ($transaction->transaction_state == 'pending')
                            <a href="{{ route('frontend.user.topup-payment', $transaction->id) }}" class="btn-upload">
                                <i class="fa fa-credit-card"></i> Pay Now
                            </a>
                        @endif
                        <a href="{{ route('frontend.user.wallet') }}" class="btn-renew">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-id">
                            <span class="order-label">Transaction :</span>
                            {{ $transactionTitle }}
                        </div>
                        <div class="payment-badge">
                            <span
                                class="{{ $isApproved ? 'text-success' : 'text-warning' }}">{{ $isApproved ? 'Payment Approved' : 'Pending Payment' }}</span>
                        </div>
                    </div>

                    <div class="order-card-body">
                        <div class="summary-lines">
                            <div class="summary-line">
                                <div class="summary-line-label">Pending Date</div>
                                <div class="summary-line-value">
                                    {{ optional($transaction->created_at)->format('Y-m-d') }}
                                </div>
                            </div>
                            <div class="summary-line">
                                <div class="summary-line-label">Remaining Balance</div>
                                <div class="summary-line-value">{{ number_format($transaction->customerWallet->balance) }}
                                    MMK</div>
                            </div>

                            <div class="summary-line">
                                <div class="summary-line-label">Payment Method</div>
                                <div class="summary-line-value">
                                    Direct Bank Transfer
                                </div>
                            </div>
                        </div>
                        <div class="order-section">
                            <div class="order-section-title">{{ $transactionTitle }} Details</div>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <div class="detail-label">Requested Amount</div>
                                    <div class="detail-value">{{ number_format($transaction->amount) . ' MMK' }}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Balance After</div>
                                    <div class="detail-value">
                                        {{ $isApproved ? number_format($transaction->balance_after) : '-' }} MMK
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Transaction Type</div>
                                    <div class="detail-value">
                                        {{ $transactionTitle }}
                                    </div>
                                </div>

                            </div>

                            <div class="mt-3 text-muted">
                                {{ $isApproved ? 'Payment Approved.' : 'Waiting for payment.' }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>
@endsection
