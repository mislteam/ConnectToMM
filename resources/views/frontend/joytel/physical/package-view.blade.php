@extends('frontend.layouts.index')
@section('title', 'joytel Package View')
@section('content')
    @include('components.alert')
    <style>
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

        .quantity-wrapper {
            display: inline-flex;
            flex-wrap: nowrap;
            align-items: stretch;
            width: auto;
            max-width: 100%;
        }

        .quantity-wrapper .qty-minus,
        .quantity-wrapper .qty-plus {
            flex: 0 0 42px;
            min-width: 42px;
        }

        .quantity-wrapper input[type="number"] {
            flex: 0 0 72px;
            width: 72px;
            min-width: 72px;
        }

        .form-design .form-group {
            margin-bottom: 1rem;
        }

        .form-design .form-group>label {
            margin-bottom: 0.55rem;
        }

        #trafficType,
        #serviceDay,
        #dataPlan {
            gap: 0.6rem;
        }

        #trafficType .btn,
        #serviceDay .btn,
        #dataPlan .btn {
            margin: 0.25rem !important;
        }

        #trafficType .btn,
        #serviceDay .btn,
        #dataPlan .btn {
            border-radius: 4px;
        }

        .quantity-wrapper .btn {
            min-width: 42px;
        }

        #addToCartBtn.button_text {
            min-height: 60px;
            padding: 0 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .service-section .row.mb-5 {
            margin-bottom: 3rem !important;
        }

        .service-box .img {
            margin-bottom: 0.65rem !important;
        }

        .service-box .content {
            padding-bottom: 0.35rem;
        }

        .service-box .content h4 {
            margin-bottom: 0.2rem;
        }

        .service-box .content .text-size-16 {
            margin-bottom: 0.55rem;
        }

        .service-box .content .more {
            margin-top: 0.15rem;
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .service-section .row.mb-5 {
                margin-bottom: 2.5rem !important;
            }

            .form-design .form-group {
                margin-bottom: 0.9rem;
            }

            #trafficType,
            #serviceDay,
            #dataPlan {
                gap: 0.5rem;
            }

            .service-box .content {
                padding-bottom: 0.3rem;
            }

            .service-box .content .more {
                margin-top: 0.1rem;
            }
        }

        @media (max-width: 767px) {
            .service-section .row.mb-5 {
                margin-bottom: 2rem !important;
            }

            .quantity-wrapper {
                max-width: 100%;
                width: 100%;
            }

            .quantity-wrapper .btn {
                min-width: 38px;
            }

            .quantity-wrapper input[type="number"] {
                flex: 1 1 auto;
                width: auto;
                min-width: 0;
            }

            #trafficType,
            #serviceDay,
            #dataPlan {
                gap: 0.45rem;
            }

            .form-design .form-group {
                margin-bottom: 0.85rem;
            }

            .service-box .img {
                margin-bottom: 0.5rem !important;
            }

            .service-box .content {
                padding-bottom: 0.25rem;
            }

            .service-box .content .text-size-16 {
                margin-bottom: 0.45rem;
            }

            #addToCartBtn.button_text {
                width: 100%;
            }

            .row.mb-5>.col-lg-6.mb-5,
            .row.mb-5>.col-lg-6.col-md-6,
            .row.mb-5>.col-lg-6.col-md-6.mb-5 {
                margin-bottom: 1.5rem !important;
            }
        }
    </style>
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
                class="mb-0 text-size-16 box_span">{{ $settings['joytel_title']->value ?? 'Joytel' }}</span>
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
                    {{-- <label class="text-size-16 text">{{ $joytel->product_description }}</label> --}}
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Provider : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->provider }}</label>
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
                            <label class="font-weight-bold">Hotspot : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->hotspot }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Recharge : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->recharge }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Coverage : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                @php
                                    $displayCoverages = collect($joytel->coverage ?? [])->values();
                                @endphp
                                @foreach ($displayCoverages as $location)
                                    <label
                                        class="text">{{ $location }}{{ $displayCoverages->count() > 1 ? ',' : '' }}</label>
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
                                <label class="text">{{ $joytel->activation_type }}</label>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Delivery Time : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ $joytel->delivery_time }}</label>
                            </div>
                        </div>
                    </div> --}}
                    @php
                        $validPriceLists = $price_lists->where('exchange_rate', '>', 0);
                        $validPriceListMap = $validPriceLists->keyBy('product_code');

                        $filterValidPlans = function ($plans) use ($validPriceListMap) {
                            return collect($plans)
                                ->filter(function ($plan) use ($validPriceListMap) {
                                    return $validPriceListMap->has($plan['code'] ?? null);
                                })
                                ->values();
                        };

                        $daily_types_valid = $filterValidPlans($daily_types);
                        $total_types_valid = $filterValidPlans($total_types);
                        $unlimited_types_valid = $filterValidPlans($unlimited_types);

                        $allPlans = collect()
                            ->merge($daily_types_valid)
                            ->merge($total_types_valid)
                            ->merge($unlimited_types_valid);

                        $hasValidPlans = $allPlans->isNotEmpty();
                    @endphp
                    @if ($hasValidPlans)
                        <form class="form-design" action="{{ route('joytelpackage.cart', $joytel->id) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="joytel_type" value="physical">
                            <input type="hidden" name="sim_type" value="recharge_physical">
                            <!-- Traffic Types -->
                            <div class="form-group">
                                <label for="trafficType" class="font-weight-bold">Type of Plan</label>
                                <div id="trafficType" class="btn-group btn-group-toggle d-flex flex-wrap">
                                    @php
                                        $validTrafficTypes = collect();
                                        foreach ($traffic_types as $type) {
                                            $key = str_contains(strtolower($type), 'daily')
                                                ? 'daily'
                                                : (str_contains(strtolower($type), 'total')
                                                    ? 'total'
                                                    : 'unlimited');
                                            $plans = ${$key . '_types_valid'} ?? collect();

                                            if ($plans->isNotEmpty()) {
                                                $validTrafficTypes->push($type);
                                            }
                                        }
                                    @endphp

                                    @foreach ($validTrafficTypes as $index => $type)
                                        @php
                                            $id = strtolower(str_replace(' ', '_', $type));
                                            $label = ucfirst($type) . ' Plan';
                                        @endphp
                                        <label class="btn btn-outline-secondary m-1 {{ $index == 0 ? 'active' : '' }}">
                                            <input type="radio" name="tType" value="{{ $type }}"
                                                {{ $index == 0 ? 'checked' : '' }} id="{{ $id }}">
                                            {{ $label }}
                                        </label>
                                    @endforeach

                                </div>
                            </div>
                            <!-- end Traffic Types -->

                            <!-- Service days -->
                            @php
                                $firstCollection = null;

                                $type = null;

                                if ($validTrafficTypes->isNotEmpty()) {
                                    $firstType = strtolower($validTrafficTypes->first());
                                    if (str_contains($firstType, 'daily') && $daily_types_valid->isNotEmpty()) {
                                        $firstCollection = $daily_types_valid;
                                        $type = 'daily';
                                    } elseif (str_contains($firstType, 'total') && $total_types_valid->isNotEmpty()) {
                                        $firstCollection = $total_types_valid;
                                        $type = 'total';
                                    } elseif (
                                        str_contains($firstType, 'unlimited') &&
                                        $unlimited_types_valid->isNotEmpty()
                                    ) {
                                        $firstCollection = $unlimited_types_valid;
                                        $type = 'unlimited';
                                    }
                                }

                                // dd($firstCollection);

                                $normalizeServiceDay = function ($day) {
                                    preg_match('/\d+/', (string) $day, $matches);
                                    return isset($matches[0]) ? (int) $matches[0] : null;
                                };

                                $matchesServiceDay = function ($planDay, $selectedDay) use ($normalizeServiceDay) {
                                    $planDay = strtolower(trim((string) $planDay));

                                    if ($planDay === 'day') {
                                        return true;
                                    }

                                    if (preg_match('/charge from\s+(\d+)/i', $planDay, $matches)) {
                                        return (int) $selectedDay >= (int) $matches[1];
                                    }

                                    return $normalizeServiceDay($planDay) === $normalizeServiceDay($selectedDay);
                                };

                                $service_day_options = collect();

                                if ($firstCollection) {
                                    $service_days = $firstCollection
                                        ->where('status', 1)
                                        ->pluck('service_day')
                                        ->unique()
                                        ->values();

                                    $hasSimpleDay = $service_days->contains(
                                        fn($day) => strtolower(trim((string) $day)) === 'day',
                                    );

                                    $chargeFromDays = $service_days
                                        ->map(function ($day) {
                                            preg_match('/charge from\s+(\d+)/i', (string) $day, $matches);
                                            return isset($matches[1]) ? (int) $matches[1] : null;
                                        })
                                        ->filter()
                                        ->values();

                                    if ($hasSimpleDay) {
                                        $service_day_options = collect(range(1, 30));
                                    } elseif ($chargeFromDays->isNotEmpty()) {
                                        $service_day_options = collect(range($chargeFromDays->min(), 30));
                                    } else {
                                        $service_day_options = $service_days
                                            ->map(fn($day) => $normalizeServiceDay($day) ?? $day)
                                            ->unique()
                                            ->sort()
                                            ->values();
                                    }
                                }
                            @endphp
                            <div class="form-group">
                                <label for="serviceDaySelect" class="font-weight-bold">Service Days</label>
                                <div id="serviceDay" class="btn-group btn-group-toggle d-flex flex-wrap"
                                    data-toggle="buttons">
                                    @if ($firstCollection)

                                        @foreach ($service_day_options as $index => $day)
                                            <label
                                                class="btn btn-outline-secondary m-1 {{ $index === 0 ? 'active' : '' }}">
                                                <input type="radio" name="sday" value="{{ $day }}"
                                                    data-day="{{ $day }}" {{ $index === 0 ? 'checked' : '' }}>
                                                {{ is_numeric($day) ? $day . ' day' : $day }}
                                            </label>
                                        @endforeach

                                        {{-- @if ($type === 'daily')
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
                                             --}}

                                        {{-- Case 1: only "day" → show 1..30 --}}
                                        {{-- @if ($service_days->first() === 'day')
                                                @for ($i = 1; $i <= 30; $i++)
                                                    <label
                                                        class="btn btn-outline-secondary m-1 {{ $i === 1 ? 'active' : '' }}">
                                                        <input type="radio" name="sday"
                                                            value="{{ $i }} day" class="service-day-single"
                                                            data-day="{{ $i }}"
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
                                                        data-day="{{ $day }}"
                                                        {{ $index === 0 ? 'checked' : '' }}>
                                                    {{ $day }}
                                                </label>
                                            @endforeach
                                        @endif --}}


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
                                            $default_day = $service_day_options->first();
                                            $default_data = $firstCollection
                                                ->filter(
                                                    fn($plan) => $matchesServiceDay(
                                                        $plan['service_day'] ?? null,
                                                        $default_day,
                                                    ),
                                                )
                                                ->values();
                                            $visibleIndex = 0;
                                            $isUnlimitedType = $type === 'unlimited';
                                        @endphp
                                        @foreach ($default_data as $data)
                                            @php
                                                $extra_price = $validPriceListMap->get($data['code']);

                                                $display_data = $isUnlimitedType
                                                    ? 'Unlimited (' .
                                                        preg_replace('/^unlimited\s*/i', '', (string) $data['data']) .
                                                        ')'
                                                    : $data['data'];
                                                $isPerDay =
                                                    strtolower(trim((string) $data['service_day'])) === 'day' ||
                                                    str_contains(
                                                        strtolower((string) $data['service_day']),
                                                        'charge from',
                                                    );
                                                $selectedDayValue = is_numeric($default_day) ? (int) $default_day : 1;
                                                $displayPrice = $extra_price
                                                    ? round(
                                                        $data['price'] *
                                                            $extra_price->exchange_rate *
                                                            ($isPerDay ? $selectedDayValue : 1),
                                                    )
                                                    : 0;
                                            @endphp
                                            @if ($extra_price)
                                                <label
                                                    class="btn btn-outline-secondary m-1 rounded {{ $visibleIndex == 0 ? 'active' : '' }}">
                                                    <input type="radio" name="sdata" value="{{ $data['data'] }}"
                                                        {{ $visibleIndex == 0 ? 'checked' : '' }}
                                                        data-price="{{ $displayPrice }}"
                                                        data-description="{{ $data['product_description'] }}"
                                                        data-memo="{{ $data['memo'] }}"
                                                        data-product-code="{{ $data['code'] }}">{{ $display_data }}
                                                </label>
                                                @php $visibleIndex++; @endphp
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <!-- end plan data -->
                            {{--  --}}
                            <!-- Qty Field -->
                            <div class="form-group">
                                <label class="font-weight-bold">Quantity</label>

                                <div class="input-group quantity-wrapper" data-editable="1">
                                    <button class="btn btn-outline-secondary qty-minus" type="button" disabled>-</button>
                                    <input type="number" id="qty" name="qty"
                                        class="form-control text-center text-dark" value="1" min="1"
                                        max="100" readonly>
                                    <button class="btn btn-outline-secondary qty-plus" type="button" disabled>+</button>
                                </div>

                            </div>

                            <div class="form-group d-flex flex-column gap-2">
                                <label class="font-weight-bold">Description</label>
                                <label class="text" id="plan-description">-</label>
                            </div>
                            @if (!empty($data['memo']))
                                <div class="form-group d-flex flex-column gap-2">
                                    <label class="font-weight-bold">Memo</label>
                                    <label class="text" id="plan-memo">-</label>
                                </div>
                            @endif
                            <div class="form-group">
                                <p id="priceDisplay" class="h5">
                                </p>
                            </div>
                            <input type="hidden" name="display_price" id="display_price" value>
                            <input type="hidden" name="product_code" id="product_code" value>
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
                        @if ($loop->iteration > 3)
                            @break
                        @endif
                        @php
                            $lowest_price = App\Models\JoytelPhysical::where('product_name', $ran_package->product_name)
                                ->where('status', 1)
                                ->get()
                                ->map(function ($plan) {
                                    $priceList = \App\Models\PriceList::where('product_code', $plan->code)->first();

                                    $exchangeRate = (float) ($priceList->exchange_rate ?? 0);
                                    $priceCny = (float) ($plan->price ?? 0);

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
                                    <a href="{{ route('joytel.physical.packageview', ['id' => $ran_package->id, 'sim_type' => 'recharge_physical']) }}"
                                        class="more">View
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
            const memo = document.getElementById('plan-memo');
            const displayPriceInput = document.getElementById('display_price');
            const productCodeInput = document.getElementById('product_code');
            const joytelModelType = 'physical';

            // Data from Blade
            const trafficTypesData = {
                'total': @json($total_types_valid),
                'unlimited': @json($unlimited_types_valid),
                'daily': @json($daily_types_valid)
            };

            const priceLists = @json($price_lists); // price_lists table

            // Helper: Find exchange rate for a specific product code
            function getProductExchangeRate(productCode) {
                const item = priceLists.find(p => p.product_code === productCode);
                return item ? item.exchange_rate : null;
            }

            // Helper: Check if product exists in price_lists table
            function isProductValid(productCode) {
                const item = priceLists.find(p => p.product_code === productCode);
                return !!(item && Number(item.exchange_rate) > 0);
            }

            function calculateTotal(priceCny, productCode, selectedDay = 1, isPerDay = false) {
                const item = priceLists.find(p => p.product_code === productCode);
                const exchangeRate = Number(item?.exchange_rate || 0);

                if (exchangeRate > 0) {
                    let base = priceCny * exchangeRate;
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
                    productCodeInput.value = selectedData.dataset.productCode || '';
                    des.innerText = selectedData.dataset.description;
                    memo.innerText = selectedData.dataset.memo;
                }
            }

            function setSelectedDataPlan(label) {
                if (!label || !dataBox.contains(label)) return;

                const input = label.querySelector('input[name="sdata"]');
                if (!input) return;

                dataBox.querySelectorAll('label').forEach(item => item.classList.remove('active'));
                label.classList.add('active');
                input.checked = true;
                updatePriceDisplay();
            }

            function normalizeText(text) {
                return String(text).trim().toLowerCase();
            }

            function formatPlanDataLabel(data, isUnlimited) {
                const text = String(data ?? '');

                if (!isUnlimited || joytelModelType === 'physical') {
                    return text;
                }

                return `Unlimited ${text.replace(/^unlimited\s*/i, '')}`;
            }

            function renderDataPlans(plans, selectedDay, selectedType) {
                dataBox.innerHTML = '';

                const validPlans = plans.filter(plan => {
                    return matchServiceDay(plan.service_day, selectedDay);
                });

                if (validPlans.length === 0) {
                    dataBox.innerHTML = '<span class="text-danger">No plans available.</span>';
                    total_price.innerText = '';
                    des.innerText = '-';
                    return;
                }

                const isUnlimitedType = normalizeText(selectedType).includes('unlimited');

                validPlans.forEach((plan, index) => {
                    const serviceText = String(plan.service_day || '').toLowerCase();
                    const isPerDay = serviceText === 'day' || serviceText.includes('charge from');

                    const dayValue = normalizeDay(selectedDay);

                    const calculatedPrice = calculateTotal(plan.price, plan.code, dayValue,
                        isPerDay);

                    const label = document.createElement('label');
                    label.className =
                        `btn btn-outline-secondary m-1 rounded ${index === 0 ? 'active' : ''}`;
                    const displayData = formatPlanDataLabel(plan.data, isUnlimitedType);
                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'sdata';
                    input.value = plan.data || '';
                    input.dataset.price = calculatedPrice;
                    input.dataset.description = plan.product_description || '';
                    input.dataset.memo = plan.memo || '';
                    input.dataset.productCode = plan.code || '';
                    input.checked = index === 0;

                    label.appendChild(input);
                    label.appendChild(document.createTextNode(` ${displayData}`));
                    dataBox.appendChild(label);
                });

                const firstInput = dataBox.querySelector('input[name="sdata"]');
                if (firstInput) {
                    firstInput.checked = true;
                    const firstLabel = firstInput.closest('label');
                    if (firstLabel) {
                        firstLabel.classList.add('active');
                    }
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

                // FILTER FIRST by valid product code
                const filteredPlans = allPlans.filter(plan => isProductValid(plan.code));

                // Extract unique normalized days
                let uniqueDays = [];

                const hasSimpleDay = filteredPlans.some(plan =>
                    String(plan.service_day || '').toLowerCase() === 'day'
                );

                const chargeFromMatch = filteredPlans.map(plan => {
                        const match = String(plan.service_day || '').toLowerCase().match(/charge from (\d+)/);
                        return match ? parseInt(match[1]) : null;
                    })
                    .filter(Boolean);

                if (hasSimpleDay) {
                    // per day
                    uniqueDays = Array.from({
                        length: 30
                    }, (_, i) => i + 1);
                } else if (chargeFromMatch.length > 0) {
                    // Charge from X days
                    const start = Math.min(...chargeFromMatch);
                    uniqueDays = Array.from({
                        length: 30 - start + 1
                    }, (_, i) => start + i);
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
                    renderDataPlans(filteredPlans, uniqueDays[0], selectedType);
                } else {
                    dataBox.innerHTML = '<span class="text-danger">No plans available.</span>';
                    des.innerText = '-';
                    total_price.innerText = '';
                }
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
                label.addEventListener('click', function() {

                    document.querySelectorAll('#trafficType label').forEach(l => l.classList.remove(
                        'active'));

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

                //renderDataPlans(trafficTypesData[key], input.value);
                const filteredPlans = trafficTypesData[key].filter(plan => isProductValid(plan
                    .code));
                renderDataPlans(filteredPlans, input.value, type);
            });

            dataBox.addEventListener('change', function(e) {
                if (e.target && e.target.name === 'sdata') {
                    updatePriceDisplay();
                }
            });

            dataBox.addEventListener('click', function(e) {
                const label = e.target.closest('label');
                if (!label || !dataBox.contains(label)) return;

                setSelectedDataPlan(label);
            });

            ['.qty-plus', '.qty-minus'].forEach(selector => {
                document.querySelector(selector).addEventListener('click', updatePriceDisplay);
            });

            renderServiceDays();
        });
    </script>
@endsection
