@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')

@section('content')
    @include('components.alert')
    <x-banner key="payment" />

    @php
        $currentStatusLabel = $orders->first()?->customer_status_label ?? 'Pending Payment';
        $statusView = $payment_status_view ?? [];
        $canShowPaymentButton =
            $statusView['show_payment_button'] ??
            false && strtolower((string) ($statusView['badge'] ?? $currentStatusLabel)) !== 'cancelled';
        $detailRoute =
            $payment_detail_route ?? route('customer.roam.order.detail', ['outerOrderId' => $outer_order_id]);
        $uploadRoute = $payment_upload_route ?? route('roam.payment.upload-slip', ['outerOrderId' => $outer_order_id]);

        $bankAccounts = [
            [
                'bank' => 'KBZ Pay',
                'account_name' => 'U Myint',
                'account_number' => '09452856556',
            ],
            [
                'bank' => 'AYA Pay',
                'account_name' => 'U Myint',
                'account_number' => '09452856556',
            ],
        ];
    @endphp

    <style>
        .order-summary .order-box {
            height: 100%;
        }

        .order-summary .button_text {
            min-width: 0;
        }

        .bank-line-box {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 14px 16px;
            background: #fafafa;
            margin-bottom: 15px;
        }

        .bank-line-box:last-child {
            margin-bottom: 0;
        }

        .bank-line-box label {
            display: block;
            margin-bottom: 4px;
        }

        .payment-info-row {
            margin-bottom: 18px;
        }

        .payment-info-row:last-child {
            margin-bottom: 0;
        }

        .payment-info-row-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .payment-info-main {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .payment-info-label {
            display: inline-block;
            margin-right: 8px;
            color: #555;
            font-weight: 600;
        }

        .payment-info-value {
            color: #1f2b6c;
            font-weight: 700;
            word-break: break-word;
        }

        .payment-layout {
            max-width: 1080px;
            margin: 0 auto;
        }

        .payment-main-column {
            width: 100%;
        }

        .payment-summary-block {
            padding-bottom: 20px;
        }

        .payment-section-block {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e4e7ee;
        }

        .payment-section-title {
            display: block;
            margin-bottom: 12px;
            color: #1f2b6c;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .payment-status-card {
            padding: 0;
            background: transparent;
            box-shadow: none;
            margin-bottom: 20px;
        }

        .payment-status-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .payment-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0;
            border-radius: 0;
            background: transparent;
            color: #9a6311;
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1;
            margin-bottom: 0;
        }

        .payment-status-title {
            margin-bottom: 8px;
            color: #1d1d1d;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .payment-status-message {
            margin-bottom: 0;
            color: #525252;
            line-height: 1.65;
        }

        .order-summary .form-design a.payment-secondary-link,
        .order-summary .form-design a.payment-secondary-link:hover,
        .order-summary .form-design a.payment-secondary-link:focus,
        .order-summary .form-design a.payment-secondary-link:focus-visible,
        .order-summary .form-design a.payment-secondary-link:active,
        .order-summary .form-design a.payment-secondary-link:visited {
            display: inline-block !important;
            color: #1f2b6c;
            font-weight: 600;
            text-decoration: underline !important;
            text-underline-offset: 3px;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            background: none !important;
            font-size: 1rem;
            line-height: inherit;
            box-shadow: none !important;
            border-radius: 0 !important;
            min-width: 0 !important;
            width: auto !important;
            height: auto !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            vertical-align: baseline;
            gap: 0 !important;
            justify-content: flex-start !important;
        }

        .payment-secondary-link:hover {
            color: #16204f;
            text-decoration: underline !important;
        }

        .payment-secondary-link:focus,
        .payment-secondary-link:focus-visible,
        .payment-secondary-link:active,
        .payment-secondary-link:visited {
            color: #1f2b6c;
            text-decoration: underline !important;
            border: none !important;
            background: none !important;
            box-shadow: none !important;
            outline: none !important;
            outline-offset: 0 !important;
        }

        .payment-secondary-link::-moz-focus-inner {
            border: 0 !important;
            padding: 0 !important;
        }

        .payment-instruction-card {
            border: 1px solid #d9e2ff;
            border-radius: 10px;
            padding: 18px 18px 8px;
            background: linear-gradient(180deg, #f8faff 0%, #fdfdff 100%);
            box-shadow: 0 10px 30px rgba(31, 43, 108, 0.06);
        }

        .payment-instruction-intro {
            margin-bottom: 14px;
            color: #43508c;
            font-weight: 600;
            font-size: 0.88rem;
        }

        .payment-instruction-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .payment-instruction-step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .payment-instruction-step:last-child {
            margin-bottom: 0;
        }

        .payment-instruction-number {
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1f2b6c;
            color: #fff;
            font-weight: 700;
            font-size: 0.76rem;
            line-height: 1;
        }

        .payment-instruction-text {
            flex: 1;
            margin: 2px 0 0;
            color: #3d3d3d;
            line-height: 1.55;
            font-size: 0.86rem;
        }

        .payment-instruction-text strong {
            color: #1f2b6c;
        }

        .payment-upload-actions {
            margin-top: 20px !important;
        }

        .order-summary .payment-upload-actions .button_text {
            width: auto;
            min-width: 130px;
            padding: 12px 24px;
            font-size: 0.95rem;
            line-height: 1.2;
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .order-summary .order-box {
                padding: 1rem;
            }

            .order-summary .table th,
            .order-summary .table td {
                padding: 0.65rem 0.45rem;
            }
        }

        @media (max-width: 767px) {
            .payment-layout {
                max-width: 100%;
            }

            .payment-info-row-status {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }

            .payment-status-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }

            .order-summary .order-box {
                padding: 0.9rem;
            }

            .order-summary .row.services-data {
                row-gap: 1rem;
            }

            .order-summary .table {
                font-size: 0.92rem;
            }

            .order-summary .table th,
            .order-summary .table td {
                padding: 0.55rem 0.35rem;
                vertical-align: top;
            }

            .order-summary .button_text {
                width: 100%;
            }

            .order-summary .payment-upload-actions {
                text-align: center !important;
                margin-top: 16px !important;
            }

            .order-summary .payment-upload-actions .button_text {
                width: auto;
                min-width: 0;
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .payment-instruction-card {
                padding: 16px 14px 6px;
            }

            .payment-instruction-step {
                gap: 10px;
            }

            .payment-instruction-number {
                flex-basis: 28px;
                width: 28px;
                height: 28px;
                font-size: 0.7rem;
            }

            .payment-instruction-intro,
            .payment-instruction-text {
                font-size: 0.82rem;
            }

            .payment-status-card {
                padding: 16px 14px;
            }

            .payment-status-title {
                font-size: 1rem;
            }

            .payment-status-message {
                font-size: 0.9rem;
            }
        }
    </style>

    <section class="order-summary">
        <div class="container payment-layout">
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="subheading" data-aos="fade-right">
                        <h6>Payment</h6>
                        <h2>Complete Payment</h2>
                    </div>
                </div>
            </div>

            <div class="row services-data justify-content-center" data-aos="fade-up">
                <div class="col-lg-10 col-md-11 col-sm-12 col-12 payment-main-column">
                    <div class="order-box form-design message_content">
                        <div class="payment-status-card is-{{ $statusView['tone'] ?? 'warning' }}">
                            <div class="payment-status-header">
                                <span class="payment-status-badge">{{ $statusView['badge'] ?? $currentStatusLabel }}</span>
                                <a href="{{ $detailRoute }}" class="payment-secondary-link">View order details</a>
                            </div>
                            <div class="payment-status-title">{{ $statusView['title'] ?? 'Please complete your payment.' }}
                            </div>
                            <p class="payment-status-message">{{ $statusView['message'] ?? '' }}</p>
                        </div>

                        <div class="payment-summary-block">
                            <div class="payment-info-row">
                                <span class="payment-info-label">Order ID :</span>
                                <span class="payment-info-value">{{ $outer_order_id }}</span>
                            </div>

                            <div class="payment-info-row payment-info-row-status">
                                <div class="payment-info-main">
                                    <span class="payment-info-label">Status :</span>
                                    <span class="payment-info-value">{{ $currentStatusLabel }}</span>
                                </div>
                            </div>

                            <div class="payment-info-row">
                                <span class="payment-info-label">Total Amount :</span>
                                <span class="payment-info-value">{{ number_format((float) $total) }} MMK</span>
                            </div>

                            <div class="payment-info-row">
                                <span class="payment-info-label">Payment Method :</span>
                                <span class="payment-info-value">{{ $payment_method ?? '-' }}</span>
                            </div>
                        </div>

                        @if ($statusView['show_bank_accounts'] ?? false)
                            <div class="payment-section-block">
                                <span class="payment-section-title">Bank Accounts</span>
                                @foreach ($bankAccounts as $account)
                                    <div class="bank-line-box">
                                        <label><strong>Bank:</strong> {{ $account['bank'] }}</label>
                                        <label><strong>Account Name:</strong> {{ $account['account_name'] }}</label>
                                        <label><strong>Account Number:</strong> {{ $account['account_number'] }}</label>
                                        <label><strong>Payment Note:</strong> Please add your order ID
                                            {{ $outer_order_id }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($statusView['show_payment_guide'] ?? false)
                            <div class="payment-section-block">
                                <span class="payment-section-title">Payment Instruction</span>
                                <div class="payment-instruction-card">
                                    <p class="payment-instruction-intro mb-0">Please complete these steps to confirm your
                                        payment:</p>
                                    <ol class="payment-instruction-list">
                                        <li class="payment-instruction-step">
                                            <span class="payment-instruction-number">1</span>
                                            <p class="payment-instruction-text">Transfer the
                                                <strong>exact total amount</strong> to any one of the admin bank accounts
                                                shown above.
                                            </p>
                                        </li>
                                        <li class="payment-instruction-step">
                                            <span class="payment-instruction-number">2</span>
                                            <p class="payment-instruction-text">Add your order ID
                                                <strong>{{ $outer_order_id }}</strong> in the payment note or description
                                                so
                                                we can match your transfer quickly.
                                            </p>
                                        </li>
                                        <li class="payment-instruction-step">
                                            <span class="payment-instruction-number">3</span>
                                            <p class="payment-instruction-text">Upload a clear <strong>JPG or PNG</strong>
                                                payment slip using the form below.</p>
                                        </li>
                                        <li class="payment-instruction-step">
                                            <span class="payment-instruction-number">4</span>
                                            <p class="payment-instruction-text">Our admin team will review and approve the
                                                payment before provisioning starts.</p>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        @endif

                        @if ($canShowPaymentButton)
                            <div class="payment-section-block">
                                <span class="payment-section-title">{{ $payment_method ?? 'Payment' }}</span>
                                <a href="{{ $statusView['payment_button_url'] ?? ($payment_action_url ?? '#') }}"
                                    class="button_text" @if (!empty($statusView['payment_button_url'] ?? $payment_action_url)) target="_self" @endif>
                                    {{ $statusView['payment_button_text'] ?? 'Continue to Payment' }}
                                </a>
                            </div>
                        @endif

                        @if ($statusView['show_upload_form'] ?? false)
                            <div class="form-group mb-0">
                                <label>{{ $statusView['upload_label'] ?? 'Upload Payment Slip' }} <span class="required"
                                        aria-hidden="true">*</span></label>
                                <form method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="payment_slip" class="form_style text-dark"
                                        accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
                                    @error('payment_slip')
                                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                                    @enderror
                                    <small class="text-muted d-block mt-2">
                                        Only JPG or PNG files are allowed. Maximum size 5MB.
                                    </small>
                                    <div class="payment-upload-actions text-right">
                                        <button type="submit"
                                            class="button_text">{{ $statusView['upload_button_text'] ?? 'Upload Slip' }}</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
