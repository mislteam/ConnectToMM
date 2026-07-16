@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <style>
        .custom-close {
            filter: invert(14%) sepia(87%) saturate(7186%) hue-rotate(359deg) brightness(97%) contrast(114%);
            width: 20px;
            height: 20px;
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
    </style>
    <x-banner key="order" />
    <section class="order-summary">
        <div class="container">
            @php
                $cartItems = collect($cartItems ?? session()->get('joytel_cart', []))
                    ->filter()
                    ->values();
                $subtotal = $cartItems->sum(fn($item) => (float) ($item['price'] ?? 0));
            @endphp
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="subheading" data-aos="fade-right">
                        <h6>Order</h6>
                        <h2>Order Summary</h2>
                    </div>
                </div>
            </div>
            <div class="row services-data" id="cart-container" data-aos="fade-up">
                @if ($cartItems->isNotEmpty())
                    <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                        <div class="order-box">
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="px-0"></th>
                                            <th>
                                                <p class="mb-0 text-size-16 font-weight-bold text-dark">Product</p>
                                            </th>
                                            <th>
                                                <p class="mb-0 text-size-16 font-weight-bold text-dark">Amount</p>
                                            </th>
                                            <th>
                                                <p class="mb-0 text-size-16 font-weight-bold text-dark">Qty</p>
                                            </th>
                                            <th>
                                                <p class="mb-0 text-size-16 font-weight-bold text-dark">Subtotal</p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cartItems as $key => $order)
                                            @php
                                                $serviceType = strtolower((string) ($order['joytel_type'] ?? 'esim'));
                                                $simType = strtolower((string) ($order['sim_type'] ?? ''));
                                                $orderType = strtolower(
                                                    (string) ($order['order_type'] ??
                                                        (str_contains($simType, 'recharge') ? 'recharge' : 'new')),
                                                );
                                                $isQuantityEditable =
                                                    $serviceType === 'esim' &&
                                                    $orderType === 'new' &&
                                                    !str_contains($simType, 'recharge');
                                                $unitPrice = (float) ($order['ori_price'] ?? ($order['price'] ?? 0));
                                            @endphp
                                            <tr>
                                                <td class="px-0">
                                                    <button type="button" data-key="{{ $key }}"
                                                        class="btn-close trash-bin custom-close"></button>
                                                </td>
                                                <td>
                                                    <h6 style="text-transform: none;">{{ $order['product_name'] ?? '-' }}
                                                    </h6>
                                                    <label>Type Of Plan :
                                                        {{ $order['plan_type_label'] ?? (($order['plan_type'] ?? '') !== '' ? $order['plan_type'] . ' Plan' : '-') }}</label><br>
                                                    <label>Service Day :
                                                        {{ (int) ($order['service_day'] ?? 1) > 1 ? $order['service_day'] . ' days' : ($order['service_day'] ?? 1) . ' day' }}</label><br>
                                                    <label>Data : {{ $order['service_data'] ?? '-' }}</label><br>
                                                    {{-- <label>SIM Type :
                                                        {{ $order['sim_type_label'] ?? Str::headline($simType ?: $orderType . ' ' . $serviceType) }}</label><br> --}}


                                                    <label>Service Type :
                                                        {{ Str::headline($order['joytel_type'] ?? 'esim') }}</label><br>
                                                    <label>Order Type :
                                                        {{ Str::headline($order['order_type'] ?? 'new') }}</label><br>
                                                    {{-- <label>Product Code : {{ $order['product_code'] ?? '-' }}</label> --}}
                                                </td>
                                                <td>
                                                    <p class="mb-0 text-size-16 item-price"
                                                        data-price="{{ $unitPrice }}">
                                                        {{ number_format($unitPrice) }}
                                                        MMK
                                                    </p>
                                                </td>
                                                <td>
                                                    @if ($isQuantityEditable)
                                                        <div class="input-group quantity-wrapper"
                                                            data-key="{{ $key }}" data-editable="1">
                                                            <button class="btn btn-outline-secondary qty-minus"
                                                                type="button">-</button>
                                                            <input type="number" value="{{ (int) ($order['qty'] ?? 1) }}"
                                                                class="form-control text-center text-dark qty-input"
                                                                min="1" max="100" name="qty">
                                                            <button class="btn btn-outline-secondary qty-plus"
                                                                type="button">+</button>
                                                        </div>
                                                    @else
                                                        <span class="quantity-static">1</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <p class="mb-0 text-size-16 total-price">
                                                        {{ number_format((float) ($order['price'] ?? 0)) }} MMK
                                                    </p>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="5">
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
                                            <p class="mb-0 text-size-16">Subtotal</p>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-size-16 subtotal">{{ number_format($subtotal) }} MMK</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="mb-0 text-size-16">Discount</p>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-size-16">-</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="mb-0 text-size-16">Total</p>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-size-16 total">{{ number_format($subtotal) }} MMK</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-4 text-center">
                                <a href="{{ route('joytelpackage.checkout') }}" class="button_text"
                                    data-request-loader>Proceed To Checkout</a>
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
        const headerCartEl = document.getElementById('order_count');
        const subtotalElement = document.querySelector('.subtotal');
        const totalElement = document.querySelector('.total');
        const pendingQuantityUpdates = new Map();

        function parseMoney(text) {
            return parseInt(String(text).replace('MMK', '').replace(/,/g, '').trim()) || 0;
        }

        function updateSubtotal() {
            let subtotal = 0;

            document.querySelectorAll('.total-price').forEach(item => {
                subtotal += parseMoney(item.innerText);
            });

            if (subtotalElement) {
                subtotalElement.innerText = subtotal.toLocaleString() + ' MMK';
            }

            if (totalElement) {
                totalElement.innerText = subtotal.toLocaleString() + ' MMK';
            }
        }

        async function syncCartQuantity(key, qty) {
            const request = fetch(`{{ url('/joytel-package/cart') }}/${key}`, {
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

            const response = window.requestLoader ? await window.requestLoader.track(request) : await request;

            if (!response.ok) {
                throw new Error('Unable to update cart quantity.');
            }

            return response.json();
        }

        function refreshCartKeys() {
            document.querySelectorAll('tbody tr').forEach((row, index) => {
                const trashButton = row.querySelector('.trash-bin');
                const quantityWrapper = row.querySelector('.quantity-wrapper');

                if (trashButton) {
                    trashButton.dataset.key = index;
                }

                if (quantityWrapper) {
                    quantityWrapper.dataset.key = index;
                }
            });
        }

        document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {
            const minus = wrapper.querySelector('.qty-minus');
            const plus = wrapper.querySelector('.qty-plus');
            const input = wrapper.querySelector('input');
            const row = wrapper.closest('tr');
            const itemPrice = row.querySelector('.item-price');
            const totalPrice = row.querySelector('.total-price');
            const originalPrice = parseInt(itemPrice.dataset.price) || parseMoney(itemPrice.innerText);

            function updatePrice() {
                let qty = parseInt(input.value) || 1;
                qty = Math.max(1, Math.min(100, qty));
                input.value = qty;

                const total = originalPrice * qty;
                totalPrice.innerText = total.toLocaleString() + ' MMK';
                updateSubtotal();

                const key = wrapper.dataset.key;
                const syncPromise = syncCartQuantity(key, qty).catch(() => {
                    // Keep the UI responsive even if the session sync fails.
                });

                pendingQuantityUpdates.set(key, syncPromise);
                syncPromise.finally(() => pendingQuantityUpdates.delete(key));
            }

            input.addEventListener('change', updatePrice);
            input.addEventListener('input', updatePrice);
            plus.addEventListener('click', () => {
                input.value = (parseInt(input.value) || 1) + 1;
                updatePrice();
            });

            minus.addEventListener('click', () => {
                const currentQty = parseInt(input.value) || 1;
                if (currentQty > 1) {
                    input.value = currentQty - 1;
                    updatePrice();
                }
            });
        });

        document.querySelectorAll('.trash-bin').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();

                const key = this.dataset.key;
                const row = this.closest('tr');

                const request = fetch(`/joytel-package/remove-cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content,
                        'Accept': 'application/json',
                    },
                });

                const response = window.requestLoader ? await window.requestLoader.track(request) :
                    await request;

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    row.remove();
                    refreshCartKeys();
                    updateSubtotal();

                    if (headerCartEl) {
                        headerCartEl.innerText = data.count;
                        headerCartEl.dataset.orderCount = data.count;
                    }

                    if (document.querySelectorAll('.trash-bin').length === 0) {
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
