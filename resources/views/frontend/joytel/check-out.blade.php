@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    <!-- Sub-Banner -->
    <x-banner key="checkout" />
    <!--Services section-->
    <section class="order-summary">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="subheading" data-aos="fade-right">
                        <h6>Checkout</h6>
                        <h2>Checkout</h2>
                    </div>
                </div>
            </div>
            <div class="row services-data" data-aos="fade-up">
                <div class="col-lg-7 col-md-7 col-sm-12 col-12">
                    <div class="order-box form-design message_content">
                        <h3 class="mb-4">Billing Detail</h3>
                        <form>
                            <div class="form-group mb-0">
                                <label>Full Name <span class="required" aria-hidden="true">*</span></label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Full Name"
                                    value="{{ auth()->user()->name }}">
                            </div>
                            <div class="form-group mb-0">
                                <label>E-Mail Address <span class="required" aria-hidden="true">*</span></label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Email"
                                    value="{{ auth()->user()->email }}">
                            </div>
                            <div class="form-group mb-0">
                                <label>Phone Number</label>
                                <input type="text" class="form_style text-dark" placeholder="Enter Your Phone Number"
                                    value="{{ auth()->user()->phone }}">
                            </div>

                        </form>
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
                                <tr>
                                    <td>
                                        <label>{{ $joytel->product_name }}</label><br>
                                        <label>{{ ($service_day > 1 ? $service_day . ' Days' : $service_day . ' Day') . '/ ' . $service_data }}</label>
                                    </td>
                                    <td><label> {{ number_format($price) . ' MMK' }}</label></td>
                                </tr>
                                <tr>
                                    <td><label> Subtotal</label></td>
                                    <td><label> 5,000 MMK</label></td>
                                </tr>
                                <tr>
                                    <td><label> Discount</label></td>
                                    <td><label> -</label></td>
                                </tr>
                                <tr>
                                    <td><label> Total</label></td>
                                    <td><label> 5,000 MMK</label></td>
                                </tr>
                            </tbody>
                        </table>
                        <h3 class="mb-3">Payment</h3>
                        <div class="form-group mb-0">
                            <input type="radio" checked="" value="Dinger Pay" id="optionsRadios1"
                                name="optionsRadios">
                            <label>Dinger Pay</label>
                        </div>
                        <div class="form-group mb-0">
                            <input type="checkbox"> Your personal data will be used to process your order, support your
                            experience throughout this website, and for other purposes described in our privacy policy.
                        </div>
                        <div class="mt-4 text-right">
                            <a href="#" class="button_text">Place Order</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
