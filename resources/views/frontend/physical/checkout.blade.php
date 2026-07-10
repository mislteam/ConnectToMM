@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    <style>
        .order-summary .order-box {
            /* height: 100%; */
            position: sticky;
            top: 135px;
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
                            <h3 class="mb-4">Payment</h3>
                            @if ($is_direct)
                                <div class="form-group mb-0">
                                    <input type="radio" value="direct_bank_transfer" id="paymentDirectBankTransfer"
                                        name="payment_method" required
                                        {{ old('payment_method') === 'direct_bank_transfer' ? 'checked' : '' }}
                                        {{ $is_direct ? '' : 'disabled' }}>
                                    <label for="paymentDirectBankTransfer"
                                        class="{{ $is_direct ? '' : 'text-muted' }}">Direct
                                        Bank Transfer</label>
                                </div>
                            @endif
                            @if ($is_uab)
                                <div class="form-group mb-0">
                                    <input type="radio" value="uabpay" id="paymentUabPay" name="payment_method"
                                        {{ $is_uab ? '' : 'disabled' }}
                                        {{ old('payment_method') === 'uabpay' ? 'checked' : '' }}>
                                    <label for="paymentUabPay" class="{{ $is_uab ? '' : 'text-muted' }}">UAB Pay </label>
                                </div>
                            @endif

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
@endsection
