@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    <!-- Sub-Banner -->
    <x-banner key="order" />
    <!--Services section-->
    <section class="order-summary">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="subheading" data-aos="fade-right">
                        <h6>Order</h6>
                        <h2>Order Summary</h2>
                    </div>
                </div>
            </div>
            <div class="row services-data" data-aos="fade-up">
                <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                    <div class="order-box">
                        <div class="table-responsive-sm">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            <p class="mb-0 text-size-16 font-weight-bold text-dark">Product </p>
                                        </th>
                                        <th>
                                            <p class="mb-0 text-size-16 font-weight-bold text-dark">Qty</p>
                                        </th>
                                        <th>
                                            <p class="mb-0 text-size-16 font-weight-bold text-dark">Amount</p>
                                        </th>
                                        <th>
                                            <p class="mb-0 text-size-16 font-weight-bold text-dark">Total Amount</p>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <h6>{{ $sku->country_name }}</h6>
                                            <label>Service Day :
                                                {{ $service_day > 1 ? $service_day . ' days' : $service_day . ' day' }}</label><br>
                                            <label>Data : {{ $service_data }}</label><br>
                                            @if (session('iccid_no'))
                                                <label>ICCID No: {{ session('iccid_no') }}</label><br>
                                            @endif
                                        </td>
                                        <td>{{ $qty }}</td>
                                        <td>
                                            <p class="mb-0 text-size-16">{{ number_format($price) . ' MMK' }}</p>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-size-16">{{ number_format($price) . ' MMK' }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan ="4">
                                            <div class="form-design message_content">
                                                <form method="POST">
                                                    <div class="row">
                                                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                                                            <div class="form-group mb-0">
                                                                <label class="mb-2">Do you have copoun code?</label>
                                                                <input type="text" class="form_style"
                                                                    placeholder="Enter Your copoun">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                                                            <div class="mt-4 text-center">
                                                                <a href="#" class="button_text">Apply</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                    <div class="order-box">
                        <h3 class="mb-4">Cart Total</h3>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="mb-0 text-size-16"> Subtotal</p>
                                    </td>
                                    <td>
                                        <p class="mb-0 text-size-16">{{ number_format($price) . ' MMK' }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="mb-0 text-size-16"> Discount</p>
                                    </td>
                                    <td>
                                        <p class="mb-0 text-size-16"> -</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="mb-0 text-size-16"> Total</p>
                                    </td>
                                    <td>
                                        <p class="mb-0 text-size-16"> 5,000 MMK</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-4 text-center">
                            <a href="{{ route('roam.checkout') }}" class="button_text">Proceed To Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
