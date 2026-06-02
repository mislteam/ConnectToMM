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

        .quantity-static {
            min-width: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            background: #f8f9fa;
            color: #212529;
            font-weight: 500;
        }

        .custom-close {
            filter: invert(14%) sepia(87%) saturate(7186%) hue-rotate(359deg) brightness(97%) contrast(114%);
            width: 20px;
            height: 20px;
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .order-summary #cart-container > .col-md-8,
            .order-summary #cart-container > .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .order-summary #cart-container > .col-md-4 {
                margin-top: 1rem;
            }

            .order-summary .order-box {
                padding: 1rem;
            }

            .order-summary .table-responsive-sm {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .order-summary .table {
                min-width: 860px;
            }

            .order-summary .table th,
            .order-summary .table td {
                padding: 0.65rem 0.45rem;
            }

            .order-summary .quantity-wrapper {
                min-width: 130px;
            }

            .order-summary #proceed-to-checkout {
                width: 100%;
                display: inline-flex;
                justify-content: center;
            }
        }

        @media (max-width: 767px) {
            .order-summary .order-box {
                padding: 0.9rem;
            }

            .order-summary .table {
                font-size: 0.92rem;
            }

            .order-summary .table th,
            .order-summary .table td {
                padding: 0.55rem 0.35rem;
                vertical-align: top;
            }

            .order-summary .quantity-wrapper {
                max-width: 100%;
            }

            .order-summary .button_text,
            .order-summary #proceed-to-checkout {
                width: 100%;
            }

            .order-summary #proceed-to-checkout {
                display: inline-flex;
                justify-content: center;
            }
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
            <div class="row services-data" id="cart-container" data-aos="fade-up">
                @if (session()->get('roam_order_cart', []))
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
                                        @foreach ($cartItems ?? session()->get('roam_order_cart', []) as $key => $order)
                                            @php
                                                $isQuantityEditable = array_key_exists(
                                                    'can_adjust_quantity',
                                                    $order,
                                                )
                                                    ? !empty($order['can_adjust_quantity'])
                                                    : strtolower((string) ($order['service_type'] ?? '')) === 'esim' &&
                                                        strtolower((string) ($order['order_type'] ?? '')) === 'new';
                                            @endphp
                                            <tr>
                                                <td class="px-0">
                                                    <button data-key="{{ $key }}"
                                                        class="btn-close trash-bin custom-close"></button>
                                                </td>

                                                <td>
                                                    <h6 class="font-weight-bold" style="text-transform: none;">
                                                        {{ $order['country_name'] }}</h6>
                                                    <label>Type Of Plan :
                                                        {{ $order['plan_type_label'] ?? (($order['plan_type'] ?? '') !== '' ? ($order['plan_type'] . ' Plan') : '-') }}</label><br>
                                                    <label>Service Day :
                                                        {{ $order['service_day'] > 1 ? $order['service_day'] . ' days' : $order['service_day'] . ' day' }}</label><br>
                                                    <label>Data : {{ $order['service_data'] }}</label><br>
                                                    <label>SIM Type :
                                                        {{ $order['sim_type_label'] ?? Str::headline($order['sim_type']) }}</label><br>
                                                    <label>Service Type :
                                                        {{ Str::headline($order['service_type'] ?? 'esim') }}</label><br>
                                                    <label>Order Type :
                                                        {{ Str::headline($order['order_type'] ?? 'new') }}</label><br>
                                                    {{-- @if ($order['iccid_exist'])
                                                        <label>ICCID No: {{ $order['iccid_no'] ?? '-' }}</label><br>
                                                    @endif --}}

                                                </td>

                                                <td>
                                                    <p class="mb-0 text-size-14 item-price"
                                                        data-price="{{ $order['ori_price'] }}">
                                                        {{ number_format((int) $order['ori_price']) . ' MMK' }}
                                                    </p>
                                                </td>
                                                <td>
                                                    @if ($isQuantityEditable)
                                                        <div class="input-group quantity-wrapper"
                                                            data-key="{{ $key }}" data-editable="1">
                                                            <button class="btn btn-outline-secondary qty-minus"
                                                                type="button">-</button>
                                                            <input type="number" value="{{ $order['qty'] }}"
                                                                class="form-control text-center text-dark qty-input"
                                                                value="1" min="1" max="100" name="qty">
                                                            <button class="btn btn-outline-secondary qty-plus"
                                                                type="button">+</button>
                                                        </div>
                                                    @else
                                                        <span class="quantity-static">1</span>
                                                    @endif
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
                                            <p class="mb-0 text-size-16 total"> 5,000 MMK</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-4 text-center">
                                <a href="{{ route('roam.esim.checkout') }}" class="button_text"
                                    id="proceed-to-checkout" data-request-loader>Proceed To Checkout</a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-lg-12">
                        <p class="text-center">Cart is Empty!</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <script>
        let subtotalElement = document.querySelector('.subtotal');
        let totalElement = document.querySelector('.total');
        let pendingQuantityUpdates = new Map();
        let headerCartEl = document.getElementById('order_count');

        async function syncCartQuantity(key, qty) {
            const request = fetch(`{{ url('/roam/esim/cart') }}/${key}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    qty
                }),
            });

            let response = window.requestLoader ? await window.requestLoader.track(request) : await request;

            if (!response.ok) {
                throw new Error('Unable to update cart quantity.');
            }

            return response.json();
        }

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
            let key = wrapper.dataset.key;
            let editable = wrapper.dataset.editable === '1';

            if (!editable) {
                input.value = 1;
                minus.disabled = true;
                plus.disabled = true;
                input.readOnly = true;
                return;
            }

            let row = wrapper.closest('tr');

            let itemPrice = row.querySelector('.item-price');
            let totalPrice = row.querySelector('.total-price');

            let originalPrice = parseInt(itemPrice.dataset.price);

            function updatePrice() {

                let qty = parseInt(input.value) || 1;

                let total = originalPrice * qty;

                totalPrice.innerText = total.toLocaleString() + ' MMK';

                updateSubtotal();

                if (key) {
                    let syncPromise = syncCartQuantity(key, qty)
                        .catch(() => {
                            // Keep the UI responsive even if the session sync fails.
                        });

                    pendingQuantityUpdates.set(key, syncPromise);
                    syncPromise.finally(() => pendingQuantityUpdates.delete(key));
                }
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

                const request = fetch(`/roam/esim/remove-cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        'Accept': 'application/json'
                    }
                });

                let response = window.requestLoader
                    ? await window.requestLoader.track(request)
                    : await request;

                let data = await response.json();

                if (data.success) {
                    row.remove();
                    headerCartEl.innerText = parseInt(headerCartEl.innerText) - 1;
                    updateSubtotal();

                    let remainingItems = document.querySelectorAll('.trash-bin').length;

                    if (remainingItems === 0) {
                        document.getElementById('cart-container').innerHTML = `
                        <div class="col-lg-12">
                            <p class="text-center">Cart is Empty!</p>
                        </div>
                    `;
                    }
                }
            });
        });
    </script>
@endsection
