@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <style>
        .order-summary .order-box {
            height: 100%;
        }

        .order-summary .button_text {
            min-width: 0;
        }
    </style>
    <x-banner key="checkout" />
    <section class="order-summary">
        <div class="container">
            @php
                $checkoutItems = collect($selectedCartItems ?? ($cartItems ?? []))
                    ->filter()
                    ->values();
                $subtotal = $subtotal ?? $checkoutItems->sum(fn($item) => (float) ($item['price'] ?? 0));
                $requiresSnCode = function ($item) {
                    $serviceType = strtolower((string) ($item['joytel_type'] ?? $item['service_type'] ?? ''));
                    $simType = strtolower((string) ($item['sim_type'] ?? ''));
                    $orderType = strtolower((string) ($item['order_type'] ?? ''));

                    return $serviceType === 'physical'
                        && ($orderType === 'recharge' || str_contains($simType, 'recharge'));
                };
            @endphp
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="subheading" data-aos="fade-right">
                        <h6>Checkout</h6>
                        <h2>Checkout</h2>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('joytel.place-order') }}" novalidate>
                @csrf
                <div class="row services-data" data-aos="fade-up">
                    <div class="col-lg-7 col-md-7 col-sm-12 col-12">
                        <div class="order-box form-design message_content">
                            <h3 class="mb-4">Billing Detail</h3>
                            <div class="form-group mb-0">
                                <label>Full Name <span class="required" aria-hidden="true">*</span></label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Full Name"
                                    value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="form-group mb-0">
                                <label>E-Mail Address <span class="required" aria-hidden="true">*</span></label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Email"
                                    value="{{ auth()->user()->email }}" readonly>
                            </div>
                            <div class="form-group mb-0">
                                <label>Phone Number <span class="required" aria-hidden="true">*</span></label>
                                <input type="text" name="phone" class="form_style text-dark"
                                    placeholder="Enter Your Phone Number" value="{{ old('phone', auth()->user()->phone) }}">
                                @error('phone')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                            @foreach ($checkoutItems as $index => $item)
                                @if ($requiresSnCode($item))
                                    <div class="form-group mb-0">
                                        <label>
                                            SN Code <span class="required" aria-hidden="true">*</span>
                                        </label>
                                        <input type="text" name="source_sn_codes[{{ $index }}]"
                                            class="form_style text-dark" placeholder="Enter SIM SN Code"
                                            value="{{ old('source_sn_codes.' . $index, $item['source_sn_code'] ?? '') }}">
                                        <small class="text-muted d-block mt-1">
                                            {{ $item['product_name'] ?? 'Physical SIM Recharge' }}
                                            @if (!empty($item['product_code']))
                                                ({{ $item['product_code'] }})
                                            @endif
                                        </small>
                                        @error('source_sn_codes.' . $index)
                                            <small class="text-danger d-block mt-2">{{ $message }}</small>
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
                                        <td><label>Product</label></td>
                                        <td><label>Subtotal</label></td>
                                    </tr>
                                    @foreach ($checkoutItems as $item)
                                        <tr>
                                            <td>
                                                <label>{{ $item['product_name'] ?? '-' }}</label><br>
                                                <label>{{ ($item['service_data'] ?? '-') . ' / ' . ((int) ($item['service_day'] ?? 1) > 1 ? $item['service_day'] . ' Days' : ($item['service_day'] ?? 1) . ' Day') }}</label><br>
                                                <label>{{ Str::headline($item['joytel_type'] ?? 'esim') }}</label><br>
                                                <label>Product Code : {{ $item['product_code'] ?? '-' }}</label>
                                            </td>
                                            <td><label>{{ number_format((float) ($item['price'] ?? 0)) }} MMK</label></td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td><label>Subtotal</label></td>
                                        <td><label>{{ number_format((float) $subtotal) }} MMK</label></td>
                                    </tr>
                                    <tr>
                                        <td><label>Discount</label></td>
                                        <td><label>-</label></td>
                                    </tr>
                                    <tr>
                                        <td><label>Total</label></td>
                                        <td><label>{{ number_format((float) $subtotal) }} MMK</label></td>
                                    </tr>
                                </tbody>
                            </table>
                            <h3 class="mb-4">Payment</h3>
                            @if ($is_direct)
                                <div class="form-group mb-0">
                                    <input type="radio" value="direct_bank_transfer" id="paymentDirectBankTransfer"
                                        name="payment_method" required {{ $is_direct ? '' : 'disabled' }}
                                        {{ old('payment_method') === 'direct_bank_transfer' ? 'checked' : '' }}>
                                    <label for="paymentDirectBankTransfer"
                                        class="{{ $is_direct ? '' : 'text-muted' }}">Direct
                                        Bank
                                        Transfer</label>
                                </div>
                            @endif
                            @if ($is_uab)
                                <div class="form-group mb-0">
                                    <input type="radio" value="UAB Pay" id="paymentUabPay" name="payment_method"
                                        {{ $is_uab ? '' : 'disabled' }}>
                                    <label for="paymentUabPay" class="{{ $is_uab ? '' : 'text-muted' }}">UAB Pay
                                    </label>
                                </div>
                            @endif
                            {{-- <div class="form-group mb-0">
                                <input type="radio" value="direct_bank_transfer" id="paymentDirectBankTransfer"
                                    name="payment_method" required
                                    {{ old('payment_method', 'direct_bank_transfer') === 'direct_bank_transfer' ? 'checked' : '' }}>
                                <label for="paymentDirectBankTransfer">Direct Bank Transfer</label>
                            </div>
                            <div class="form-group mb-0">
                                <input type="radio" value="uab_pay" id="paymentUabPay" name="payment_method"
                                    {{ old('payment_method') === 'uab_pay' ? 'checked' : '' }}>
                                <label for="paymentUabPay">UAB Pay</label>
                            </div> --}}
                            <div class="form-group mb-0">
                                <small class="text-muted d-block">
                                    Choose Direct Bank Transfer or UAB Pay for this order.
                                </small>
                                @error('payment_method')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <input type="checkbox" name="terms" required> Your personal data will be used to process
                                your order, support your experience throughout this website, and for other purposes
                                described in our privacy policy.
                                @error('terms')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
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
@endsection
