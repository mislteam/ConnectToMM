@extends('frontend.layouts.index')
@section('title', 'joytel Package View')
@section('content')
    <!-- Sub-Banner -->
    <div class="sub-banner">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="{{ asset('assets/images/homebanner-bgshape.png') }}" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>{{ $joytel->product_name }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Our Service</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16">{{ $joytel_type_label }}</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">Joytel</span>
        </div>
    </div>
    <!--Services section-->
    <section class="service-section">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6 col-md-6 mb-5">
                    {{-- carousel indicators --}}
                    @if ($joytel->photo == null)
                        <div id="productGlleryIndicators" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#productGlleryIndicators" data-slide-to="0" class="active">
                                    <img class="d-block w-100 border"
                                        src="{{ asset('assets/images/default_sim_image.png') }}">
                                </li>
                                <li data-target="#productGlleryIndicators" data-slide-to="1">
                                    <img class="d-block w-100 border"
                                        src="{{ asset('assets/images/default_sim_image.png') }}">
                                </li>
                                <li data-target="#productGlleryIndicators" data-slide-to="2">
                                    <img class="d-block w-100 border"
                                        src="{{ asset('assets/images/default_sim_image.png') }}">
                                </li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img class="d-block w-100" src="{{ asset('assets/images/default_sim_image.png') }}">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="{{ asset('assets/images/default_sim_image.png') }}">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="{{ asset('assets/images/default_sim_image.png') }}">
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#productGlleryIndicators" role="button"
                                data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#productGlleryIndicators" role="button"
                                data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    @else
                        <div id="productGlleryIndicators" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                @foreach ($joytel->photo as $index => $photo)
                                    <li data-target="#productGlleryIndicators" data-slide-to="{{ $index }}"
                                        class="{{ $index == 0 ? 'active' : '' }}">
                                        <img class="d-block w-100 border img-fluid" src="{{ asset('sim/' . $photo) }}"
                                            style="height: 80px;background-size:cover;object-fit:cover;">
                                    </li>
                                @endforeach
                            </ol>
                            <div class="carousel-inner">
                                @foreach ($joytel->photo as $index => $photo)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <img class="d-block w-100 img-fluid" src="{{ asset('sim/' . $photo) }}"
                                            style="height: 380px;background-size:cover;object-fit:cover;">
                                    </div>
                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#productGlleryIndicators" role="button"
                                data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#productGlleryIndicators" role="button"
                                data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-lg-6 col-md-6">
                    <h4>{{ $joytel->product_name }}</h4>
                    <label class="text-size-16 text">{{ $joytel->description }}</label>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Provider : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->supplier }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Network : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                @php
                                    $count = $network_types->count();
                                @endphp
                                @foreach ($network_types as $network_type)
                                    <label class="text">{{ $network_type }}{{ $count > 1 ? ',' : '' }}</label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Coverage : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                @foreach ($joytel->usage_location as $location)
                                    <label
                                        class="text">{{ $location }}{{ count($joytel->usage_location) > 1 ? ',' : '' }}</label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Activation Policy : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->activation_policy }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Delivery Time : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->delivery_time }}</label>
                            </div>
                        </div>
                    </div>
                    @php
                        $priceListCodes = $price_lists
                            ->where('exchange_rate', '>', 0)
                            ->pluck('product_code')
                            ->toArray();
                        $allPlans = collect()
                            ->merge($daily_types)
                            ->merge($total_types)
                            ->merge($unlimited_types);
                        $hasValidPlans = $allPlans->contains(function ($plan) use ($priceListCodes) {
                            return in_array($plan['product_code'], $priceListCodes);
                        });
                    @endphp
                    @if ($hasValidPlans)
                    <form class="form-design" action="#" method="GET">
                        <!-- Traffic Types -->
                        <div class="form-group">
                            <label for="trafficType" class="font-weight-bold">Type of Plan</label>
                            <div id="trafficType" class="btn-group btn-group-toggle d-flex flex-wrap">
                                @php
                                    $validTrafficTypes = collect();
                                    $priceListCodes = $price_lists
                                        ->where('exchange_rate', '>', 0)
                                        ->pluck('product_code')
                                        ->toArray();
                                    foreach ($traffic_types as $type) {
                                        $key = str_contains(strtolower($type), 'daily') ? 'daily' :
                                        (str_contains(strtolower($type), 'total') ? 'total' : 'unlimited');
                                        $plans = ${$key . '_types'} ?? collect();

                                        // Check if ANY plan under this type has valid product_code
                                        $hasValid = collect($plans)->contains(function ($plan) use ($priceListCodes) {
                                        return in_array($plan['product_code'], $priceListCodes);
                                        });

                                        if ($hasValid) {
                                            $validTrafficTypes->push($type);
                                        }
                                    }
                                @endphp
                                
                                @foreach ($validTrafficTypes as $index => $type)
                                    @php
                                        $id = strtolower(str_replace(' ', '_', $type));
                                    @endphp
                                    <label class="btn btn-outline-secondary m-1 {{ $index == 0 ? 'active' : '' }}">
                                        <input type="radio" name="tType" value="{{ $type }}"
                                        {{ $index == 0 ? 'checked' : '' }} id="{{ $id }}">
                                    {{ $type }}
                                    </label>
                                @endforeach
                                
                            </div>
                        </div>
                        <!-- end Traffic Types -->

                        <!-- Service days -->
                        @php
                            $firstCollection = null;
                            $service_days = collect();
                            $type = null;

                            if ($traffic_types->isNotEmpty()) {
                                $firstType = strtolower($traffic_types->first());
                                if (str_contains($firstType, 'daily') && $daily_types->isNotEmpty()) {
                                    $firstCollection = $daily_types;
                                    $type = 'daily';
                                } elseif (str_contains($firstType, 'total') && $total_types->isNotEmpty()) {
                                    $firstCollection = $total_types;
                                    $type = 'total';
                                } elseif (str_contains($firstType, 'unlimited') && $unlimited_types->isNotEmpty()) {
                                    $firstCollection = $unlimited_types;
                                    $type = 'unlimited';
                                }
                            }

                            // dd($firstCollection);

                            if ($firstCollection) {
                                $service_days = $firstCollection
                                    ->where('code_status', 1)
                                    ->pluck('service_day')
                                    ->unique()
                                    ->values();
                            }
                        @endphp
                        <div class="form-group">
                            <label for="serviceDaySelect" class="font-weight-bold">Service Days</label>
                            <div id="serviceDay" class="btn-group btn-group-toggle d-flex flex-wrap"
                                data-toggle="buttons">
                                @if ($firstCollection)
                                    @if ($type === 'daily')
                                        @php
                                            // Check if charge from exists
                                            $has_charge_from = $service_days->contains(function ($d) {
                                                return preg_match('/charge from/i', $d);
                                            });

                                            // Prepare numeric days only if charge from exists
                                            if ($has_charge_from) {
                                                $numeric_days = $service_days
                                                    ->map(function ($d) {
                                                        preg_match('/(\d+)/', $d, $matches);
                                                        return isset($matches[1]) ? (int) $matches[1] : null;
                                                    })
                                                    ->filter()
                                                    ->values();

                                                $start = $numeric_days->min(); // first available charge day
                                            }
                                        @endphp

                                        {{-- Case 1: only "day" → show 1..30 --}}
                                        @if ($service_days->first() === 'day')
                                            @for ($i = 1; $i <= 30; $i++)
                                                <label
                                                    class="btn btn-outline-secondary m-1 {{ $i === 1 ? 'active' : '' }}">
                                                    <input type="radio" name="sday" value="{{ $i }} day"
                                                        class="service-day-single" data-day="{{ $i }}"
                                                        {{ $i === 1 ? 'checked' : '' }}>
                                                    {{ $i }} day
                                                </label>
                                            @endfor
                                        @elseif ($has_charge_from)
                                            @for ($i = $start; $i <= 30; $i++)
                                                <label
                                                    class="btn btn-outline-secondary m-1 {{ $i === $start ? 'active' : '' }}">
                                                    <input type="radio" name="sday" value="{{ $i }}"
                                                        data-day="{{ $i }}"
                                                        {{ $i === $start ? 'checked' : '' }}>
                                                    {{ $i }} day
                                                </label>
                                            @endfor
                                        @else
                                            @foreach ($service_days as $index => $day)
                                                <label
                                                    class="btn btn-outline-secondary m-1 {{ $index === 0 ? 'active' : '' }}">
                                                    <input type="radio" name="sday" value="{{ $day }}"
                                                        data-day="{{ $day }}"
                                                        {{ $index === 0 ? 'checked' : '' }}>
                                                    {{ $day }}
                                                </label>
                                            @endforeach
                                        @endif
                                    @else
                                        @foreach ($service_days as $index => $day)
                                            <label
                                                class="btn btn-outline-secondary m-1 {{ $index === 0 ? 'active' : '' }}">
                                                <input type="radio" name="sday" value="{{ $day }}"
                                                    data-day="{{ $day }}" {{ $index === 0 ? 'checked' : '' }}>
                                                {{ $day }}
                                            </label>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </div>
                        <!-- end Service days -->

                        <!-- Plan Data -->
                        <div class="form-group">
                            <label class="font-weight-bold">Data</label>
                            <div id="dataPlan" class="btn-group btn-group-toggle d-flex flex-wrap"
                                data-toggle="buttons">
                                @if ($firstCollection)
                                    @php
                                        $default_day = $firstCollection->pluck('service_day')->first();
                                        $default_data = $firstCollection->where('service_day', $default_day)->values();
                                        $priceListMap = $price_lists->keyBy('product_code');
                                    @endphp
                                    @foreach ($default_data as $key => $data)
                                        @php
                                            $extra_price = $priceListMap[$data['product_code']] ?? null;
                                        @endphp
                                        <label
                                            class="btn btn-outline-secondary m-1 rounded {{ $key == 0 ? 'active' : '' }}">
                                            <input type="radio" name="sdata" value="{{ $data['data'] }}"
                                                {{ $key == 0 ? 'checked' : '' }}
                                                data-price="{{ $extra_price ? round($data['price_cny'] * $extra_price->exchange_rate) : 0 }}"
                                                data-description="{{ $data['description'] }}"
                                                data-product-code="{{ $data['product_code'] }}">{{ $data['data'] }}
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <!-- end plan data -->
                        {{--  --}}
                        <!-- Qty Field -->
                        <div class="form-group">
                            <label class="font-weight-bold">Quantity</label>
                            <div class="input-group quantity-wrapper">
                                <button class="btn btn-outline-secondary qty-minus" type="button">-</button>
                                <input type="number" id="qty" class="form-control text-center" value="1"
                                    min="1" max="100" name="qty">
                                <button class="btn btn-outline-secondary qty-plus" type="button">+</button>
                            </div>
                        </div>

                        <div class="form-group d-flex flex-column gap-2">
                            <lable class="font-weight-bold">Description</lable>
                            <label class="text" id="plan-description">-</label>
                        </div>
                        <div class="form-group">
                            <p id="priceDisplay" class="h5">
                            </p>
                        </div>
                        <input type="hidden" name="display_price" id="display_price" value>
                        <!-- Add to Cart -->
                        <button type="submit" id="addToCartBtn" class="button_text">Add To
                            Cart</button>
                    </form>
                    @else
                    <div class="alert alert-warning">This plan is currently not available for sale.</div>
                    @endif
                </div>
            </div>

            <!-- Random card section -->
            <h4 class="mb-4">You may also like</h4>
            <div class="services-data">
                <div class="row">
                    @foreach ($random_packages as $ran_package)
                        @php
                            $lowest_price = collect($ran_package->plan ?? [])
                                ->map(function ($plan) {
                                    $priceList = \App\Models\PriceList::where(
                                        'product_code',
                                        $plan['product_code'] ?? null,
                                    )->first();

                                    $exchangeRate = (float) ($priceList->exchange_rate ?? 0);
                                    $priceCny = (float) ($plan['price_cny'] ?? 0);

                                    if ($exchangeRate <= 0 || $priceCny <= 0) {
                                        return null;
                                    }

                                    return round($priceCny * $exchangeRate);
                                })
                                ->filter(fn($price) => $price > 0)
                                ->min();
                        @endphp
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="service-box">
                                <figure class="img img2 mb-3">
                                    @if ($ran_package->photo === null || empty($ran_package->photo))
                                        <img src="{{ asset('assets/images/default_sim_image.png') }}" alt="default sim"
                                            class="img-fluid">
                                    @else
                                        <img src="{{ asset('sim/' . $ran_package->photo[0]) }}" alt="data sim"
                                            class="img-fluid">
                                    @endif
                                </figure>
                                <div class="content">
                                    <h4>{{ $ran_package->product_name }}</h4>
                                    <p class="text-size-16">
                                        From
                                        {{ number_format($lowest_price) }}
                                        MMK</p>
                                    <a href="{{ route('joytel.packageview', $ran_package->id) }}" class="more">View
                                        Offer</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const hasValidPlans = @json($hasValidPlans);

    if (!hasValidPlans) {
        console.log('No valid plans. JS stopped.');

        const serviceDay = document.getElementById('serviceDay');
        const dataPlan = document.getElementById('dataPlan');
        const priceDisplay = document.getElementById('priceDisplay');

        if (serviceDay) serviceDay.innerHTML = '';
        if (dataPlan) dataPlan.innerHTML = '';
        if (priceDisplay) priceDisplay.innerText = '';

        return; 
    }
    const service_day_box = document.getElementById('serviceDay');
    const dataBox = document.getElementById('dataPlan');
    const total_price = document.getElementById('priceDisplay');
    const des = document.getElementById('plan-description');
    const displayPriceInput = document.getElementById('display_price');

    // Data from Blade
    const trafficTypesData = {
        'total': @json($total_types),
        'unlimited': @json($unlimited_types),
        'daily': @json($daily_types)
    };

    const priceLists = @json($price_lists); // price_lists table

    // Helper: Find exchange rate for a specific product code
    function getProductExchangeRate(productCode) {
        const item = priceLists.find(p => p.product_code === productCode);
        return item ? item.exchange_rate : null;
    }

    // Helper: Check if product exists in price_lists table
    function isProductValid(productCode) {
        return priceLists.some(p => p.product_code === productCode);
    }

    function calculateTotal(priceCny, productCode, selectedDay = 1, isPerDay = false) {
        const item = priceLists.find(p => p.product_code === productCode);

        if (item && item.exchange_rate) {
            let base = priceCny * item.exchange_rate;
            if (isPerDay) {
                return Math.round(base * selectedDay);
            }
            return Math.round(base);
        }

        return 0;
    }

    function updatePriceDisplay() {
        const selectedData = dataBox.querySelector('input[name="sdata"]:checked');
        const qty = parseInt(document.getElementById('qty').value) || 1;

        if (selectedData) {
            const pricePerUnit = parseFloat(selectedData.dataset.price);
            const total = pricePerUnit * qty;
            total_price.innerText = `Total Price: ${total.toLocaleString()} MMK`;
            displayPriceInput.value = total;
            des.innerText = selectedData.dataset.description;
        }
    }

    function normalizeText(text) {
        return String(text).trim().toLowerCase();
    }

    function renderDataPlans(plans, selectedDay) {
        dataBox.innerHTML = '';

        const validPlans = plans.filter(plan => {
            return matchServiceDay(plan.service_day, selectedDay);
        });

        console.log('Filtered Plans:', validPlans);

        if (validPlans.length === 0) {
            dataBox.innerHTML = '<span class="text-danger">No plans available.</span>';
            total_price.innerText = '';
            des.innerText = '-';
            return;
        }

        validPlans.forEach((plan, index) => {
            const serviceText = plan.service_day.toLowerCase();
            const isPerDay = serviceText === 'day' || serviceText.includes('charge from');

            const dayValue = normalizeDay(selectedDay);

            const calculatedPrice = calculateTotal(plan.price_cny,plan.product_code,dayValue,isPerDay);

            const label = document.createElement('label');
            label.className = `btn btn-outline-secondary m-1 rounded ${index === 0 ? 'active' : ''}`;
            label.innerHTML = `
                <input type="radio" name="sdata" value="${plan.data}"
                    data-price="${calculatedPrice}" 
                    data-description="${plan.description}" 
                    data-product-code="${plan.product_code}"
                    ${index === 0 ? 'checked' : ''}>
                ${plan.data}
            `;
            dataBox.appendChild(label);
        });

        const firstInput = dataBox.querySelector('input[name="sdata"]');
        if (firstInput) {
            firstInput.checked = true;
            updatePriceDisplay();
        }
    }

    function renderServiceDays() {
        const selectedType = document.querySelector('#trafficType input[name="tType"]:checked').value;
        const typeValue = normalizeText(selectedType);

        let key = 'daily';
        if (typeValue.includes('total')) key = 'total';
        else if (typeValue.includes('unlimited')) key = 'unlimited';

        const allPlans = trafficTypesData[key];

        // FILTER FIRST by valid product_code
        const filteredPlans = allPlans.filter(plan => isProductValid(plan.product_code));

        // Extract unique normalized days
        let uniqueDays = [];

        const hasSimpleDay = filteredPlans.some(plan =>
            plan.service_day.toLowerCase() === 'day'
        );

        const chargeFromMatch = filteredPlans.map(plan => {
            const match = plan.service_day.toLowerCase().match(/charge from (\d+)/);
            return match ? parseInt(match[1]) : null;
        })
        .filter(Boolean);

        if (hasSimpleDay) {
            // per day
            uniqueDays = Array.from({ length: 30 }, (_, i) => i + 1);
        } else if (chargeFromMatch.length > 0) {
            // Charge from X days
            const start = Math.min(...chargeFromMatch);
            uniqueDays = Array.from({ length: 30 - start + 1 }, (_, i) => start + i);
        } else {
            // Normal case
            uniqueDays = [...new Set(
            filteredPlans.map(plan => normalizeDay(plan.service_day))
            )].sort((a, b) => a - b);
        }

        service_day_box.innerHTML = '';

        uniqueDays.forEach((day, index) => {
            const label = document.createElement('label');
            label.className = `btn btn-outline-secondary m-1 ${index === 0 ? 'active' : ''}`;
            label.innerHTML = `
                <input type="radio" name="sday" value="${day}" ${index === 0 ? 'checked' : ''}>
                ${day} day
            `;
            service_day_box.appendChild(label);
        });

        if (uniqueDays.length > 0) {
            renderDataPlans(filteredPlans, uniqueDays[0]);
        } else {
            dataBox.innerHTML = '<span class="text-danger">No plans available.</span>';
            des.innerText = '-';
            total_price.innerText = '';
        }
        console.log('Selected Type:', selectedType);
        console.log('Key:', key);
        console.log('All Plans:', allPlans);
    }

    function normalizeDay(day) {
        const match = String(day).match(/\d+/);
        return match ? parseInt(match[0]) : null;
    }

    function matchServiceDay(itemDay, uiDay) {
        const item = String(itemDay).toLowerCase().trim();

        // Per day
        if (item === 'day') {
            return true;
        }

        // Charge from X days
        const match = item.match(/charge from (\d+)/);
        if (match) {
            const start = parseInt(match[1]);
            return uiDay >= start;
        }

        //Normal case
        return normalizeDay(itemDay) === normalizeDay(uiDay);
    }

    document.querySelectorAll('#trafficType label').forEach(label => {
        label.addEventListener('click', function () {

            document.querySelectorAll('#trafficType label').forEach(l => l.classList.remove('active'));

            this.classList.add('active');

            const input = this.querySelector('input[name="tType"]');
            if (input) input.checked = true;
            renderServiceDays();
        });
    });

    service_day_box.addEventListener('click', function(e) {
        const label = e.target.closest('label');
        if (!label) return;

        service_day_box.querySelectorAll('label').forEach(l => l.classList.remove('active'));
        label.classList.add('active');

        const input = label.querySelector('input[name="sday"]');
        if (input) input.checked = true;

        const type = document.querySelector('#trafficType input[name="tType"]:checked').value;

        const typeValue = normalizeText(type);
        let key = 'daily';
        if (typeValue.includes('total')) key = 'total';
        else if (typeValue.includes('unlimited')) key = 'unlimited';

        console.log('Day clicked:', input.value);

        //renderDataPlans(trafficTypesData[key], input.value);
        const filteredPlans = trafficTypesData[key].filter(plan => isProductValid(plan.product_code));
        renderDataPlans(filteredPlans, input.value);
    });

    dataBox.addEventListener('change', updatePriceDisplay);

    document.querySelector('.qty-plus').addEventListener('click', () => {
        const qty = document.getElementById('qty');
        qty.value = parseInt(qty.value) + 1;
        updatePriceDisplay();
    });

    document.querySelector('.qty-minus').addEventListener('click', () => {
        const qty = document.getElementById('qty');
        if (qty.value > 1) {
            qty.value = parseInt(qty.value) - 1;
            updatePriceDisplay();
        }
    });

    renderServiceDays();
});
</script>
@endsection
