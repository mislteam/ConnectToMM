@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    <style>
        .text-size-14 {
            font-size: 14px;
            line-height: 24px;
        }

        .quantity-wrapper {
            min-width: 100px;
            max-width: 110px;
        }

        .custom-close {
            filter: invert(14%) sepia(87%) saturate(7186%) hue-rotate(359deg) brightness(97%) contrast(114%);
            width: 20px;
            height: 20px;
        }
    </style>
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
                                        <th class="px-0"></th>
                                        <th>
                                            <p class="mb-0 text-size-14 font-weight-bold text-dark pl-2">Product </p>
                                        </th>
                                        <th>
                                            <p class="mb-0 text-size-14 font-weight-bold text-dark">Amount</p>
                                        </th>
                                        <th>
                                            <p class="mb-0 text-size-14 font-weight-bold text-dark">Qty</p>
                                        </th>
                                        <th>
                                            <p class="mb-0 text-size-14 font-weight-bold text-dark">Subtotal</p>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (session()->get('roam_order_cart', []) as $key => $order)
                                        <tr>
                                            <td class="px-0">
                                                <button data-key="{{ $key }}"
                                                    class="btn-close trash-bin custom-close"></button>
                                            </td>

                                            <td>
                                                <h6 class="font-weight-bold" style="text-transform: none;">
                                                    {{ $order['country_name'] }}</h6>
                                                <label>Service Day :
                                                    {{ $order['service_day'] > 1 ? $order['service_day'] . ' days' : $order['service_day'] . ' day' }}</label><br>
                                                <label>Data : {{ $order['service_data'] }}</label><br>
                                                <label>SIM Type : {{ Str::headline($order['sim_type']) }}</label><br>
                                                @if ($order['iccid_exist'])
                                                    <label>ICCID No: {{ $order['iccid_no'] ?? '-' }}</label><br>
                                                @endif

                                            </td>

                                            <td>
                                                <p class="mb-0 text-size-14 item-price"
                                                    data-price="{{ $order['ori_price'] }}">
                                                    {{ number_format((int) $order['ori_price']) . ' MMK' }}
                                                </p>
                                            </td>
                                            <td>
                                                <div class="input-group quantity-wrapper">
                                                    <button class="btn btn-outline-secondary qty-minus"
                                                        type="button">-</button>
                                                    <input type="number" value="{{ $order['qty'] }}"
                                                        class="form-control text-center text-dark qty-input" value="1"
                                                        min="1" max="100" name="qty">
                                                    <button class="btn btn-outline-secondary qty-plus"
                                                        type="button">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-0 text-size-14 total-price">
                                                    {{ number_format((int) $order['price']) }} MMK
                                                </p>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan ="5">
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
                                        <p class="mb-0 text-size-14 subtotal"></p>
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
                                        <p class="mb-0 text-size-16 total"></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-4 text-center">
                            <a href="{{ route('roam.physical.checkout') }}" class="button_text">Proceed To Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        let subtotalElement = document.querySelector('.subtotal');
        let totalElement = document.querySelector('.total');

        function updateSubtotal() {

            let subtotal = 0;

            document.querySelectorAll('.total-price').forEach(item => {

                let priceText = item.innerText
                    .replace('MMK', '')
                    .replace(/,/g, '')
                    .trim();

                subtotal += parseInt(priceText) || 0;
            });

            subtotalElement.innerText = subtotal.toLocaleString() + ' MMK';
            totalElement.innerText = subtotal.toLocaleString() + ' MMK';
        }

        document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {

            let minus = wrapper.querySelector('.qty-minus');
            let plus = wrapper.querySelector('.qty-plus');
            let input = wrapper.querySelector('input');

            let row = wrapper.closest('tr');

            let itemPrice = row.querySelector('.item-price');
            let totalPrice = row.querySelector('.total-price');

            let originalPrice = parseInt(itemPrice.dataset.price);

            function updatePrice() {

                let qty = parseInt(input.value) || 1;

                let total = originalPrice * qty;

                totalPrice.innerText = total.toLocaleString() + ' MMK';

                updateSubtotal();
            }

            input.addEventListener('change', updatePrice);
            input.addEventListener('input', updatePrice);
            plus.addEventListener('click', () => {
                input.value = parseInt(input.value || 1) + 1;
                updatePrice();
            });

            minus.addEventListener('click', () => {
                let currentQty = parseInt(input.value || 1);
                if (currentQty > 1) {
                    input.value = currentQty - 1;
                    updatePrice();
                }

            });
        });

        updateSubtotal();

        document.querySelectorAll('.trash-bin').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();

                let key = this.dataset.key;

                let row = this.closest('tr');

                let response = await fetch(`/roam/physical/remove-cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        'Accept': 'application/json'
                    }
                });

                let data = await response.json();

                if (data.success) {
                    row.remove();
                    updateSubtotal();
                }
            });
        });
    </script>
@endsection
