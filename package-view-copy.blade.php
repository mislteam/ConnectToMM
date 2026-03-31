@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
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
                class="mb-0 text-size-16">E-SIM</span><span class="mb-0 text-size-16 dash">-</span><span
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
                    <h4>{{ $joytel->category_name }}</h4>
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
                    <form class="form-design" action="{{ route('joytelpackage.cart', $joytel->id) }}" method="GET">
                        <!-- Traffic Types -->
                        <div class="form-group">
                            <label for="trafficType" class="font-weight-bold">Type of traffic</label>
                            <div id="trafficType" class="btn-group btn-group-toggle d-flex flex-wrap"
                                data-toggle="buttons">
                                @foreach ($traffic_types as $index => $type)
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
                                    $type = 'unlimi ted';
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

                            // dd($service_days);

                        @endphp

                        <div class="form-group">
                            <label for="serviceDaySelect" class="font-weight-bold">Service Days</label>
                            <div id="serviceDay" class="btn-group btn-group-toggle d-flex flex-wrap"
                                data-toggle="buttons">
                                @if ($firstCollection)
                                    @if ($type === 'daily')
                                        @php
                                            $numeric_days = $service_days
                                                ->map(fn($d) => (int) filter_var($d, FILTER_SANITIZE_NUMBER_INT))
                                                ->filter();
                                        @endphp

                                        {{-- Case 1: only "day" → show 1..30 --}}
                                        @if ($service_days->count() === 1 && $service_days->first() === 'day')
                                            @for ($i = 1; $i <= 30; $i++)
                                                <label
                                                    class="btn btn-outline-secondary m-1 {{ $i === 1 ? 'active' : '' }}">
                                                    <input type="radio" name="sday" value="{{ $i }} day"
                                                        data-day="{{ $i }}" {{ $i === 1 ? 'checked' : '' }}>
                                                    {{ $i }} day
                                                </label>
                                            @endfor
                                            {{-- Case 2: only one numeric day (eg: 3 day) → show from that day..30 --}}
                                        @elseif ($numeric_days->count() === 1)
                                            @php $start = $numeric_days->first(); @endphp
                                            @for ($i = $start; $i <= 30; $i++)
                                                <label
                                                    class="btn btn-outline-secondary m-1 {{ $i === $start ? 'active' : '' }}">
                                                    <input type="radio" name="sday" value="{{ $i }}"
                                                        data-day="{{ $i }}"
                                                        {{ $i === $start ? 'checked' : '' }}>
                                                    {{ $i }} day
                                                </label>
                                            @endfor
                                            {{-- Case 3: multiple different numeric days → show only those --}}
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
                                        {{-- Total / Unlimited → just display their service days --}}
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
                                        // dd($priceListMap);
                                    @endphp
                                    @foreach ($default_data as $key => $data)
                                        @php
                                            // $price = $cny_currency
                                            //     ? $cny_currency->value *
                                            //         $data['price_cny'] *
                                            //         (1 + $profit_percentage / 100)
                                            //     : $data['price_cny'];

                                            $price = $cny_currency->value * $data['price_cny'];

                                            $extra_price = $priceListMap[$data['product_code']] ?? 0;
                                            // dd($price + $extra_price->price);
                                        @endphp
                                        <label
                                            class="btn btn-outline-secondary m-1 rounded {{ $key == 0 ? 'active' : '' }}">
                                            <input type="radio" name="sdata" value="{{ $data['data'] }}"
                                                {{ $key == 0 ? 'checked' : '' }}
                                                data-price="{{ $extra_price ? $price + $extra_price->price : $price }}"
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
                </div>
            </div>

            <!-- Random card section -->
            <h4 class="mb-4">You may also like</h4>
            <div class="services-data">
                <div class="row">
                    @foreach ($random_packages as $ran_package)
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
                                    <p class="text-size-16">From 1,000 MMK</p>
                                    <a href="{{ route('joytel.packageview', $ran_package->id) }}" class="more">View
                                        Offer</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- end random card section -->

        </div>
    </section>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const service_day_box = document.getElementById('serviceDay');
        const dataBox = document.getElementById('dataPlan');

        const total_price = document.getElementById('priceDisplay');
        const des = document.getElementById('plan-description');

        // Traffic type data from Blade
        const tServiceDays = @json($total_types->where('code_status', 1)->pluck('service_day')->unique());
        const UServiceDays = @json($unlimited_types->where('code_status', 1)->pluck('service_day')->unique());
        const dServiceDays = @json($daily_types);
        // console.log(tServiceDays, UServiceDays, dServiceDays);
        const totalTypes = @json($total_types);
        const unlimitedTypes = @json($unlimited_types);
        const dailyTypes = @json($daily_types);
        // additonal price list
        const price_lists = @json($price_lists);
        // currency
        const cny_currency = @json($cny_currency);

        const checkdDays = @json($daily_types->where('code_status', 1)->pluck('service_day')->unique());
        const dCount = checkdDays.length;
        const displayPrice = document.getElementById('display_price');
        // console.log(displayPrice);

        // Cache the HTML of each type if pre-rendered by Blade
        let defaultHtml = {
            daily: null,
            total: null,
            unlimited: null
        };

        const selected = document.querySelector('#trafficType input[name="tType"]:checked')?.value;

        if (selected === 'Daily Type') {
            defaultHtml.daily = {
                days: service_day_box.innerHTML,
                data: dataBox.innerHTML
            };
        } else if (selected === 'Total Type') {
            defaultHtml.total = {
                days: service_day_box.innerHTML,
                data: dataBox.innerHTML
            };
        } else if (selected === 'Unlimited Type') {
            defaultHtml.unlimited = {
                days: service_day_box.innerHTML,
                data: dataBox.innerHTML
            };
        }

        // Helper
        const getActiveTrafficType = () => {
            const val = document.querySelector('#trafficType input[name="tType"]:checked')?.value;
            if (val === 'Total Type') return 'total';
            if (val === 'Unlimited Type') return 'unlimited';
            return 'daily';
        };

        const mapPlan = item => ({
            data: item.data,
            price_cny: item.price_cny,
            plan_description: item.description,
            product_code: item.product_code
        });

        // Render service days for a type
        function renderServiceDays(days, type) {
            if (!days) return;
            service_day_box.innerHTML = '';
            dataBox.innerHTML = '';

            days.forEach((day, index) => {
                const label = document.createElement('label');
                label.className = 'btn btn-outline-secondary m-1';

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'sday';
                input.value = day;
                input.dataset.day = day;

                if (index === 0) {
                    input.checked = true;
                    label.classList.add('active');

                    // Pre-render data for first day
                    let dataToRender = [];
                    if (type === 'total') dataToRender = totalTypes.filter(i => String(i
                        .service_day) === String(day)).map(mapPlan);
                    else if (type === 'unlimited') dataToRender = unlimitedTypes.filter(i => String(i
                        .service_day) === String(day)).map(mapPlan);
                    else if (type === 'daily') dataToRender = dailyTypes.filter(i => String(i
                        .service_day) === String(day)).map(mapPlan);
                    renderData(dataToRender);
                }

                label.appendChild(input);
                label.appendChild(document.createTextNode(day));
                service_day_box.appendChild(label);
            });
        }

        // Render data options
        function renderData(data) {
            dataBox.innerHTML = '';
            data.forEach((value, index) => {
                const label = document.createElement('label');
                label.className = 'btn btn-outline-secondary m-1 rounded';

                const matched_code = price_lists.find((code) => value.product_code === code
                    .product_code);
                // console.log(matched_code);
                var original_price = value.price_cny * cny_currency.value;

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'sdata';
                input.value = value.data;
                input.dataset.price = matched_code ? original_price + matched_code.price :
                    original_price;
                input.dataset.description = value.plan_description;

                if (index === 0) {
                    input.checked = true;
                    label.classList.add('active');
                    total_price.textContent =
                        `Total Price: ${ matched_code ? original_price + matched_code.price :
                    original_price} MMK`;
                    displayPrice.value = matched_code ? original_price + matched_code.price :
                        original_price;
                    des.textContent = value.plan_description;
                }

                label.appendChild(input);
                label.appendChild(document.createTextNode(value.data));
                dataBox.appendChild(label);
            });
        }

        // Click on service day
        service_day_box.addEventListener('click', function(e) {
            // console.log('hi');
            const label = e.target.closest('label');
            if (!label || !service_day_box.contains(label)) return;

            const input = label.querySelector('input[name="sday"]');
            if (!input) return;

            const day = input.dataset.day || input.value;
            const type = getActiveTrafficType();
            let dataToRender = [];

            if (type === 'total') dataToRender = totalTypes.filter(i => String(i.service_day) ===
                String(day)).map(mapPlan);
            else if (type === 'unlimited') dataToRender = unlimitedTypes.filter(i => String(i
                .service_day) === String(day)).map(mapPlan);
            else if (type === 'daily') {
                if (dCount === 1) return;
                dataToRender = dailyTypes.filter(i => String(i.service_day) === String(day)).map(
                    mapPlan);
            }
            renderData(dataToRender);
        });

        // Click on data → update price/description
        dataBox.addEventListener('click', function(e) {
            const label = e.target.closest('label');
            if (!label || !dataBox.contains(label)) return;

            const input = label.querySelector('input[name="sdata"]');
            if (!input) return;

            total_price.textContent = `Total Price: ${input.dataset.price} MMK`;
            displayPrice.value = input.dataset.price;
            des.textContent = input.dataset.description;
        });

        // Traffic type switching
        document.querySelectorAll('#trafficType label').forEach(label => {
            label.addEventListener('click', function() {
                const input = this.querySelector('input[name="tType"]');
                if (!input) return;

                let typeKey = '';
                let daysArray = [];

                if (input.value === 'Daily Type') {
                    typeKey = 'daily';
                    daysArray = @json($daily_types->pluck('service_day')->unique());
                } else if (input.value === 'Total Type') {
                    typeKey = 'total';
                    daysArray = @json($total_types->pluck('service_day')->unique());
                } else if (input.value === 'Unlimited Type') {
                    typeKey = 'unlimited';
                    daysArray = @json($unlimited_types->pluck('service_day')->unique());
                }

                // Restore cached HTML if exists, otherwise rebuild dynamically
                if (defaultHtml[typeKey]) {
                    service_day_box.innerHTML = defaultHtml[typeKey].days;
                    dataBox.innerHTML = defaultHtml[typeKey].data;
                } else {
                    renderServiceDays(daysArray, typeKey);
                }

                // Update price/description
                const first = dataBox.querySelector('label input[name="sdata"]');
                if (first) {
                    // console.log(first);
                    total_price.textContent = `Total Price: ${first.dataset.price} MMK`;
                    displayPrice.value = first.dataset.price;
                    des.textContent = first.dataset.description;
                }
            });
        });

        // page load and show first data ( daily or traffic or unlimited )
        (function initFirstLoad() {
            const firstData = dataBox.querySelector('label input[name="sdata"]');
            if (firstData) {
                console.log(firstData.dataset.price);
                total_price.textContent = `Total Price: ${firstData.dataset.price} MMK`;
                displayPrice.value = firstData.dataset.price;
                des.textContent = firstData.dataset.description;
            }
        })();
    });
</script>
