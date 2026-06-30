@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    @php
        $file = get_banner('joytel_esim');
        $image = $file !== null ? 'banner/' . $file : 'assets/images/default-banner.png';
        $banner = \App\Models\Banner::where('banner_type', 'joytel_esim')->first();
    @endphp
    <div class="sub-banner" style="background-image: url({{ asset($image) }})">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="{{ asset('assets/images/homebanner-bgshape.png') }}" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>{{ $banner->title ?? '' }}</h1>
                            <p>{{ $banner->subtitle ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Our Service</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16">E-SIM</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">{{ $settings['joytel_title']->value ?? 'Joytel' }}</span>
        </div>
    </div>

    @php
        $orderTabs = $orderTabs ?? [
            'new_esim' => ['label' => 'New eSIM'],
            // 'recharge_esim' => ['label' => 'Recharge'],
        ];
        $selectedSimType = $selectedSimType ?? 'new_esim';
    @endphp

    <section class="service-section esim-package-section">
        <div class="container">
            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>

            <div class="services-data mt-4">
                <div class="esim-package-panel" data-selected-sim-type="{{ $selectedSimType }}">
                    <div class="esim-order-tabs" role="tablist" aria-label="eSIM order tabs">
                        @foreach ($orderTabs as $orderType => $orderData)
                            <button type="button"
                                class="esim-order-tab {{ $selectedSimType === $orderType ? 'active' : '' }}"
                                data-order-tab="{{ $orderType }}">
                                {{ $orderData['label'] }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($orderTabs as $orderType => $orderData)
                        @php
                            $validPackages = collect($packages ?? [])
                                ->filter(fn($package) => !empty($package))
                                ->values();
                            $groupValidCount = $validPackages->count();
                        @endphp
                        <div class="esim-order-pane {{ $selectedSimType === $orderType ? 'active' : '' }}"
                            data-order-pane="{{ $orderType }}">
                            <form method="get" action="{{ route('esim.search') }}" data-request-loader
                                class="esim-search-form">
                                <input type="hidden" name="type" value="{{ $orderType }}">
                                @if ($orderType === 'recharge_esim')
                                    <input type="hidden" name="iccid_exist" value="1">
                                @endif

                                <div class="esim-search-card">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0 esim-search-form-group">
                                                <h4>Shop for the best eSIM offers</h4>
                                                <select class="select2_design form-control" multiple="multiple"
                                                    name="locations[]" data-placeholder="Search for destination..."
                                                    aria-label="Search for destination">
                                                    <option value=""></option>
                                                    @foreach ($usage_locations as $location)
                                                        <option value="{{ $location }}">{{ $location }}</option>
                                                    @endforeach
                                                </select>
                                                @error('locations[]')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mt-4 text-center">
                                                <button type="submit" class="button_text">Continue Search</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="esim-package-stage">
                                <div class="esim-package-stage__title">
                                    <h3>Package Plan</h3>
                                </div>

                                @if ($groupValidCount === 0)
                                    <div class="esim-empty-state">
                                        <p>Packages are temporarily not available.</p>
                                    </div>
                                @else
                                    <div class="esim-package-grid" id="package-list-{{ $orderType }}"
                                        data-package-grid="{{ $orderType }}">
                                        @foreach ($validPackages as $package)
                                            <div class="package-card" data-package-card="{{ $orderType }}">
                                                <div class="service-box esim-service-box">
                                                    <figure class="img img2 mb-3">
                                                        @if ($package->photo === null || empty($package->photo))
                                                            <img src="{{ asset('assets/images/default_sim_image.png') }}"
                                                                alt="default sim" class="img-fluid">
                                                        @else
                                                            <img src="{{ asset('sim/' . $package->photo[0]) }}"
                                                                alt="data sim" class="img-fluid">
                                                        @endif
                                                    </figure>
                                                    <div class="content">
                                                        <h4>{{ $package->product_name ?? '' }}</h4>
                                                        @php
                                                            $lowest_price = App\Models\JoytelEsim::where(
                                                                'product_name',
                                                                $package->product_name,
                                                            )
                                                                ->where('status', 1)
                                                                ->get()
                                                                ->map(function ($plan) {
                                                                    $priceList = \App\Models\PriceList::where(
                                                                        'product_code',
                                                                        $plan->code,
                                                                    )->first();

                                                                    $exchangeRate =
                                                                        (float) ($priceList->exchange_rate ?? 0);
                                                                    $priceCny = (float) ($plan->price ?? 0);

                                                                    if ($exchangeRate <= 0 || $priceCny <= 0) {
                                                                        return null;
                                                                    }

                                                                    return round($priceCny * $exchangeRate);
                                                                })
                                                                ->filter(fn($price) => $price > 0)
                                                                ->min();
                                                        @endphp
                                                        <p class="text-size-16">From
                                                            {{ number_format($lowest_price) }}
                                                            MMK
                                                        </p>
                                                        {{-- <a href="{{ route('joytel.packageview', ['joytel' => $package->id, 'type' => 'esim']) }}"
                                                            class="more">View Offer</a> --}}

                                                        <a href="{{ route('joytel.esim.packageview', ['id' => $package->id, 'sim_type' => $orderType]) }}"
                                                            class="more">View Offer</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($groupValidCount > 6)
                                    <div class="text-center mt-4">
                                        <button type="button" id="showMoreBtn-{{ $orderType }}"
                                            class="btn btn-primary px-4 py-2" data-show-more="{{ $orderType }}">Show
                                            All</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <figure class="element2 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-2.png') }}" class="img-fluid" alt="">
            </figure>
        </div>
    </section>
    <style>
        .esim-package-section {
            position: relative;
            overflow: hidden;
        }

        .esim-package-panel {
            position: relative;
            z-index: 1;
        }

        .esim-order-tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: stretch;
            justify-content: flex-start;
            width: min(100%, 390px);
            margin: 0.85rem 0 1.1rem 0.15rem;
        }

        .esim-order-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 156px;
            min-width: 0;
            min-height: 60px;
            padding: 0.8rem 1rem;
            border-radius: 4px;
            border: 1px solid rgba(207, 214, 226, 0.98);
            background: #ffffff;
            color: #123a86;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.22s ease;
            box-shadow: 0 6px 14px rgba(10, 36, 104, 0.05);
            letter-spacing: 0.01em;
        }

        .esim-order-tab.active {
            background: linear-gradient(135deg, #123a86, #0f4ea8);
            color: #fff;
            border: 1px solid transparent;
            outline: none;
            box-shadow: 0 14px 30px rgba(10, 36, 104, 0.2);
        }

        .esim-order-pane {
            display: none;
            padding: 0.25rem 0 0;
            margin-bottom: 1.25rem;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        .esim-order-pane.active {
            display: block;
        }

        .esim-search-card {
            padding: 1rem 1rem 0.95rem;
            background: #ffffff;
            border: 1px solid rgba(18, 58, 134, 0.12);
            border-radius: 4px;
            box-shadow: 0 6px 14px rgba(10, 36, 104, 0.05);
        }

        .esim-search-form-group h4,
        .esim-package-stage__title h3 {
            margin-bottom: 0.6rem;
            color: #251f5a;
            font-size: 1.3rem;
        }

        .esim-package-stage__title h3 {
            font-weight: 800;
        }

        .esim-search-card .select2-container,
        .esim-search-card .select2-container--bootstrap4 {
            width: 100% !important;
            display: block !important;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-selection {
            width: 100% !important;
            min-height: 60px;
            height: 60px !important;
            box-sizing: border-box;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 60px;
            height: 60px !important;
            padding: 0.5rem 0.75rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            width: 100%;
            height: 100%;
            padding-left: 0;
            margin-top: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            max-width: 100%;
            margin: 0.1rem 0 0;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-search--inline,
        .esim-search-card .select2-container--bootstrap4 .select2-search__field {
            width: auto !important;
            max-width: none !important;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-search--inline {
            display: inline-flex;
            align-items: center;
            float: none;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-search__field {
            min-width: 90px;
            margin-top: 0;
            font-size: 1rem;
            line-height: 1.3;
        }

        .esim-search-card .select2-container--bootstrap4 .select2-selection__placeholder {
            font-size: 1rem;
            line-height: 1.3;
        }

        .esim-search-card .button_text {
            min-height: 60px;
            padding: 0 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .esim-package-stage {
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(18, 58, 134, 0.08);
        }

        .esim-package-stage__title {
            margin-bottom: 18px;
            text-align: center;
        }

        .esim-package-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
            column-gap: 1rem;
            row-gap: 1.1rem;
            margin-top: 1rem;
        }

        .esim-package-grid .package-card {
            width: 100%;
            min-width: 0;
        }

        .esim-service-box {
            height: 100%;
        }

        .esim-service-box .img {
            margin-bottom: 0.65rem !important;
        }

        .esim-service-box .img img {
            width: 100%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
        }

        .esim-service-box .content {
            padding-bottom: 0.35rem;
        }

        .esim-service-box .content h4 {
            margin-bottom: 0.2rem;
        }

        .esim-service-box .content .text-size-16 {
            margin-bottom: 0.55rem;
        }

        .esim-service-box .content .more {
            margin-top: 0.15rem;
        }

        .esim-empty-state {
            padding: 2rem 1rem 1.5rem;
            text-align: center;
            color: #5e6473;
            background: transparent;
            border: 0;
        }

        .esim-empty-state p {
            margin-bottom: 0;
            font-size: 18px;
        }

        @media (max-width: 1199.98px) {
            .esim-package-section .container {
                max-width: 100%;
            }

            .esim-order-tabs {
                width: 100%;
                margin-left: 0;
            }

            .esim-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 0.9rem;
                row-gap: 0.9rem;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .esim-package-section {
                padding-top: 0.5rem;
            }

            .esim-package-panel {
                padding: 0 0.25rem;
            }

            .esim-order-tabs {
                width: 100%;
                gap: 0.65rem;
                margin: 0.75rem 0 1rem;
            }

            .esim-order-tab {
                min-height: 56px;
                flex-basis: 150px;
                font-size: 0.9rem;
            }

            .esim-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 0.9rem;
                row-gap: 0.9rem;
            }

            .esim-search-card {
                padding: 0.95rem 0.95rem 0.9rem;
            }

            .esim-search-card .button_text {
                min-width: 220px;
            }

            .esim-package-stage {
                margin-top: 1.25rem;
            }
        }

        @media (max-width: 767.98px) {
            .esim-package-section {
                padding-top: 1.5rem;
            }

            .esim-package-panel {
                padding: 0;
            }

            .esim-package-section .element1,
            .esim-package-section .element2 {
                opacity: 0.35;
                transform: scale(0.8);
            }

            .esim-package-section .services-data {
                margin-top: 1.5rem !important;
            }

            .esim-order-tabs {
                width: 100%;
                margin: 1.1rem 0 1rem 0;
                gap: 0.6rem;
            }

            .esim-order-tab {
                flex: 1 1 100%;
                min-height: 52px;
                padding: 0.55rem 0.75rem;
                border-radius: 4px;
                font-size: 0.88rem;
            }

            .esim-package-grid {
                grid-template-columns: 1fr;
            }

            .esim-service-box .content {
                padding-bottom: 0.25rem;
            }

            .esim-order-pane {
                padding: 0.2rem 0 0;
            }

            .esim-search-card {
                padding: 0.85rem 0.85rem 0.8rem;
                border-radius: 12px;
            }

            .esim-package-stage {
                padding-top: 0.35rem;
                margin-top: 1.15rem;
            }

            .esim-search-form-group h4,
            .esim-package-stage__title h3 {
                font-size: 1.1rem;
            }

            .esim-search-card .select2-container--bootstrap4 .select2-selection,
            .esim-search-card .select2-container--bootstrap4 .select2-selection--multiple,
            .esim-search-card .button_text {
                min-height: 52px;
                height: 52px !important;
            }

            .esim-search-card .select2-container--bootstrap4 .select2-selection--multiple {
                padding: 0.45rem 0.7rem;
            }

            .esim-empty-state {
                padding: 1.5rem 0.75rem 1rem;
            }

            .esim-empty-state p {
                font-size: 16px;
            }

            .esim-search-card .button_text {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderTabs = document.querySelectorAll('.esim-order-tab');
            const panes = document.querySelectorAll('.esim-order-pane');
            const activeSimType = document.querySelector('.esim-package-panel')?.dataset.selectedSimType ||
                'new_esim';

            function updateUrlParam(name, value) {
                const url = new URL(window.location.href);
                url.searchParams.set(name, value);
                window.history.replaceState({}, '', url.toString());
            }

            function setActivePane(simType) {
                orderTabs.forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.orderTab === simType);
                });

                panes.forEach(pane => {
                    pane.classList.toggle('active', pane.dataset.orderPane === simType);
                });

                const activePane = document.querySelector(`.esim-order-pane[data-order-pane="${simType}"]`);
                if (activePane) {
                    const activeSelect = $(activePane).find('.select2_design');
                    if (activeSelect.length && !activeSelect.hasClass('select2-hidden-accessible')) {
                        activeSelect.select2({
                            width: '100%'
                        });
                    }
                }

                updateUrlParam('type', simType);
            }

            setActivePane(activeSimType);

            orderTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    setActivePane(tab.dataset.orderTab || 'new_esim');
                });
            });

            document.querySelectorAll('[data-package-grid]').forEach((grid) => {
                const items = grid.querySelectorAll('.package-card');
                const gridKey = grid.getAttribute('data-package-grid');
                const showMoreBtn = document.querySelector(`[data-show-more="${gridKey}"]`);
                const visibleCount = 6;

                if (!showMoreBtn || items.length === 0) {
                    if (showMoreBtn) {
                        showMoreBtn.style.display = 'none';
                    }
                    return;
                }

                items.forEach((item, index) => {
                    if (index >= visibleCount) {
                        item.style.display = 'none';
                    }
                });

                if (items.length <= visibleCount) {
                    showMoreBtn.style.display = 'none';
                    return;
                }

                showMoreBtn.addEventListener('click', function(event) {
                    event.preventDefault();

                    items.forEach((item, index) => {
                        if (index >= visibleCount) {
                            item.style.display = '';
                        }
                    });

                    showMoreBtn.style.display = 'none';
                });
            });
        });
    </script>

    <section class="need-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content" data-aos="fade-right">
                        <h6>NEED MORE HELP?</h6>
                        <h2>Leading, Trusted. Enabling growth.</h2>
                        <p class="text-size-18">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                            tempor incididuntabore et dolore aliquaQuis ipsum suspe.</p>
                    </div>
                </div>
            </div>
            <div class="row position-relative">
                <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                    <div class="service1">
                        <figure class="img img1">
                            <img src="{{ asset('assets/images/need-sales-icon.png') }}" alt="" class="img-fluid">
                        </figure>
                        <h3>Sales</h3>
                        <p class="text-size-18">Lorem ipsum dolor sit ametcon sec tetur adipiscing elit sed</p>
                        <a href="./contact.html" class="btn">Contact Sales</a>
                    </div>
                </div>
                <figure class="arrow1 mb-0" data-aos="fade-down">
                    <img src="{{ asset('assets/images/need-arrow1.png') }}" class="img-fluid" alt="">
                </figure>
                <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                    <div class="service1 service2">
                        <figure class="img img2">
                            <img src="{{ asset('assets/images/need-more-icon2.png') }}" alt=""
                                class="img-fluid">
                        </figure>
                        <h3>Help & Support</h3>
                        <p class="text-size-18">Labore et dolore magna aliqua quis ipsum suspendisse ultrices</p>
                        <a href="./contact.html" class="btn">Get Support</a>
                    </div>
                </div>
                <figure class="arrow2 mb-0" data-aos="fade-up">
                    <img src="{{ asset('assets/images/need-arrow-2.png') }}" class="img-fluid" alt="">
                </figure>
                <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                    <div class="service1">
                        <figure class="img img3">
                            <img src="{{ asset('assets/images/need-more-icon-3.png') }}" alt=""
                                class="img-fluid">
                        </figure>
                        <h3>Article & News</h3>
                        <p class="text-size-18">viverra maecenas accumsan lacus vel facili sis consectetur adipiscing</p>
                        <a href="{{ route('Contact') }}" class="btn">Read Article</a>
                    </div>
                </div>
            </div>
        </div>
        <figure class="mb-0 need-layer">
            <img src="{{ asset('assets/images/need-layer.png') }}" alt="" class="img-fluid">
        </figure>
    </section>
@endsection
