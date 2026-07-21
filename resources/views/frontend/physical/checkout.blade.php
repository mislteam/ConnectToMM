@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <style>
        .order-summary .order-box {
            height: auto !important;
            position: sticky;
            top: 135px;
        }

        .uab-payment-methods-text.is-hidden {
            display: none !important;
        }

        .uab-payment-methods-text {
            margin-top: 0 !important;
            line-height: 1.25;
        }

        .uab-payment-option label {
            margin-bottom: 0 !important;
        }

        .wallet-payment-panel {
            border: 1px solid #e5e9f0;
            border-radius: 6px;
            padding: 10px 12px 9px;
            margin-bottom: 12px;
            background: #fff;
        }

        .wallet-payment-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #005eb8;
            font-size: 13px;
            line-height: 1.25;
        }

        .wallet-payment-heading strong {
            color: #000;
            font-size: 20px;
            font-weight: 700;
        }

        .wallet-payment-divider {
            border-top: 1px solid #d6dde8;
            margin: 8px 0;
        }

        .wallet-payment-choice {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0 !important;
            color: #555;
            font-size: 13px;
            cursor: pointer;
        }

        .other-payment-methods {
            padding-top: 2px;
            margin-bottom: 18px;
        }

        .other-payment-methods .form-group {
            margin-bottom: 10px !important;
        }
    </style>
    <!-- Sub-Banner -->
    <x-banner key="checkout" />
    <!--Services section-->
    <section class="order-summary">
        <div class="container">
            @php
                $checkoutItems = collect($selectedCartItems ?? [$cart ?? []])
                    ->filter()
                    ->values();
            @endphp
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="subheading" data-aos="fade-right">
                        <h6>Checkout</h6>
                        <h2>Checkout</h2>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('roam.place-order') }}" novalidate>
                @csrf
                <div class="row services-data" data-aos="fade-up">
                    <div class="col-lg-7 col-md-7 col-sm-12 col-12">
                        <div class="order-box form-design message_content">
                            <h3 class="mb-4">Billing Detail</h3>
                            <div class="form-group mb-0">
                                <label>Full Name <span class="required" aria-hidden="true">*</span></label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Full Name"
                                    name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}">
                                @error('customer_name')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label>E-Mail Address <span class="required" aria-hidden="true">*</span></label>
                                <input type="email" class="form_style text-dark" placeholder="Enter Your Email"
                                    name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}">
                                @error('customer_email')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label>Phone Number<span class="required" aria-hidden="true">*</span></label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Phone Number"
                                    name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone) }}">
                                @error('customer_phone')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                            @foreach ($checkoutItems as $itemIndex => $item)
                                @if (($item['iccid_count'] ?? 0) > 0)
                                    @php
                                        $orderType = strtolower((string) ($item['order_type'] ?? ''));
                                        $serviceType = strtolower((string) ($item['service_type'] ?? 'physical'));
                                        $dpInfo = (int) ($item['dp_info'] ?? 0);

                                        $expectedIccidLength = null;
                                        $acceptAnyRechargeLength = false;
                                        if ($orderType === 'recharge' && $serviceType === 'physical') {
                                            $expectedIccidLength = $dpInfo === 21 ? 18 : 19;
                                        } elseif ($orderType === 'recharge' && $serviceType === 'esim') {
                                            // For eSIM recharge we accept both Global (19) and Asia (18) ICCID formats.
                                            $acceptAnyRechargeLength = true;
                                        }
                                    @endphp
                                    <div class="form-group mb-0">
                                        <label>{{ $item['iccid_label'] ?? ($item['country_name'] ?? 'Item') . ' ICCID No' }}
                                            <span class="required" aria-hidden="true">*</span></label>
                                        <input type="text" name="iccid_numbers[{{ $itemIndex }}][]"
                                            class="form_style text-dark" placeholder="Enter ICCID No" inputmode="numeric"
                                            value="{{ old("iccid_numbers.$itemIndex.0", data_get($iccid_numbers ?? [], "$itemIndex.0", '')) }}"
                                            pattern="{{ $acceptAnyRechargeLength ? '^(?:[0-9]{18}|[0-9]{19})$' : ($expectedIccidLength ? '^[0-9]{' . $expectedIccidLength . '}$' : '^[0-9]*$') }}"
                                            minlength="{{ $acceptAnyRechargeLength ? 18 : $expectedIccidLength ?? 0 }}"
                                            maxlength="{{ $acceptAnyRechargeLength ? 19 : $expectedIccidLength ?? 255 }}"
                                            title="{{ $acceptAnyRechargeLength ? 'ICCID must be 18 or 19 digits' : ($expectedIccidLength ? 'ICCID must be ' . $expectedIccidLength . ' digits' : 'ICCID must contain digits only') }}"
                                            oninput="this.value=this.value.replace(/\\D/g,'').slice(0, {{ $acceptAnyRechargeLength ? 19 : $expectedIccidLength ?? 255 }});"
                                            onpaste="setTimeout(() => { this.value=this.value.replace(/\\D/g,'').slice(0, {{ $acceptAnyRechargeLength ? 19 : $expectedIccidLength ?? 255 }}); }, 0);"
                                            ondrop="setTimeout(() => { this.value=this.value.replace(/\\D/g,'').slice(0, {{ $acceptAnyRechargeLength ? 19 : $expectedIccidLength ?? 255 }}); }, 0);"
                                            onblur="this.value=this.value.replace(/\\D/g,'').slice(0, {{ $acceptAnyRechargeLength ? 19 : $expectedIccidLength ?? 255 }});">
                                        @error("iccid_numbers.$itemIndex.0")
                                            <small class="text-danger d-block mt-2">{!! $message !!}</small>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-12">
                        <div class="order-box">
                            <h3 class="mb-4">Your Order</h3>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><label> Product</label></td>
                                        <td><label> Subtotal</label></td>
                                    </tr>
                                    @foreach ($checkoutItems as $item)
                                        <tr>
                                            <td>
                                                <label>{{ $item['country_name'] ?? '-' }}</label><br>
                                                <label>{{ $item['plan_type_label'] ?? (($item['plan_type'] ?? '') !== '' ? $item['plan_type'] . ' Plan' : '-') }}</label><br>
                                                <label>{{ ($item['service_data'] ?? '') . ' / ' . (($item['service_day'] ?? 1) > 1 ? $item['service_day'] . ' Days' : $item['service_day'] . ' Day') }}</label><br>
                                                @php
                                                    $summaryIccidLabel = trim(
                                                        str_replace(
                                                            ['(', ')', 'ICCID No'],
                                                            '',
                                                            (string) ($item['iccid_label'] ?? ''),
                                                        ),
                                                    );
                                                    $summaryIccidLabel = \Illuminate\Support\Str::before(
                                                        $summaryIccidLabel,
                                                        ' - ',
                                                    );
                                                @endphp
                                                <label>{{ $summaryIccidLabel !== '' ? $summaryIccidLabel : $item['sim_type_label'] ?? Str::headline($item['sim_type'] ?? '') }}</label><br>
                                                @if (($item['iccid_count'] ?? 0) > 0)
                                                    <label>ICCID No: </label><br>
                                                @endif
                                            </td>
                                            <td><label> {{ number_format((float) ($item['price'] ?? 0)) . ' MMK' }}</label>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td><label> Subtotal</label></td>
                                        <td><label>{{ number_format((float) ($subtotal ?? 0)) }} MMK</label></td>
                                    </tr>
                                    <tr>
                                        <td><label> Discount</label></td>
                                        <td><label> -</label></td>
                                    </tr>
                                    <tr>
                                        <td><label> Total</label></td>
                                        <td><label>{{ number_format((float) ($subtotal ?? 0)) }} MMK</label></td>
                                    </tr>
                                </tbody>
                            </table>
                            @php
                                $selectedPaymentMethod = old(
                                    'payment_method',
                                    $is_direct ? 'direct_bank_transfer' : ($is_uab ? 'uabpay' : null),
                                );
                            @endphp
                            <h3 class="mb-4">Payment</h3>
                            @include('components.wallet-payment-option')
                            <div class="other-payment-methods">
                                @if ($is_direct)
                                    <div class="form-group mb-0">
                                        <input type="radio" value="direct_bank_transfer" id="paymentDirectBankTransfer"
                                            name="payment_method" required
                                            {{ $selectedPaymentMethod === 'direct_bank_transfer' ? 'checked' : '' }}
                                            {{ $is_direct ? '' : 'disabled' }}>
                                        <label for="paymentDirectBankTransfer"
                                            class="{{ $is_direct ? '' : 'text-muted' }}">{{ $direct_payment_name ?? 'Direct Bank Transfer' }}</label>
                                    </div>
                                @endif
                                @if ($is_uab)
                                    <div class="form-group mb-0 uab-payment-option">
                                        <input type="radio" value="uabpay" id="paymentUabPay" name="payment_method"
                                            {{ $is_uab ? '' : 'disabled' }}
                                            {{ $selectedPaymentMethod === 'uabpay' ? 'checked' : '' }}>
                                        <label for="paymentUabPay"
                                            class="{{ $is_uab ? '' : 'text-muted' }}">{{ $uab_payment_name ?? 'Online Payment' }}
                                        </label>
                                        @if (!empty($uab_payment_methods_text))
                                            <small id="uabPaymentMethodsText"
                                                class="uab-payment-methods-text d-block text-danger ms-3 {{ $selectedPaymentMethod === 'uabpay' ? '' : 'is-hidden' }}">{{ $uab_payment_methods_text }}</small>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="form-group mb-0">
                                @error('payment_method')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <input type="checkbox" name="terms" required> Your personal data will be used to process
                                your order, support your
                                experience throughout this website, and for other purposes described in our privacy policy.
                            </div>
                            <div class="mt-4 text-right">
                                <button type="submit" class="button_text">Place Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uabPaymentMethodsText = document.getElementById('uabPaymentMethodsText');

            if (!uabPaymentMethodsText) {
                return;
            }

            const toggleUabPaymentMethodsText = function() {
                const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
                uabPaymentMethodsText.classList.toggle(
                    'is-hidden',
                    !selectedPaymentMethod || selectedPaymentMethod.value !== 'uabpay'
                );
            };

            document.querySelectorAll('input[name="payment_method"]').forEach(function(paymentMethod) {
                paymentMethod.addEventListener('change', toggleUabPaymentMethodsText);
            });

            toggleUabPaymentMethodsText();
        });
    </script>
@endsection
