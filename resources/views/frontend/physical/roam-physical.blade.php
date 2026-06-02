@extends('frontend.layouts.index')
@section('title', 'Roam Physical-SIM')
@section('content')
    @include('components.alert')
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
                            <h1>Physical-SIM - {{ $settings['roam_title']->value ?? 'Roam' }}</h1>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                                labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Our Service</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16">Physical-SIM</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">{{ $settings['roam_title']->value ?? 'Roam' }}</span>
        </div>
    </div>
    <!-- Services section -->
    @php
        $orderTabs = [
            // 'new_physical' => ['label' => 'New SIM'],
            'recharge_physical' => ['label' => 'Recharge'],
        ];
        $displayOrderType = $selectedOrderType === 'new_physical' ? 'recharge_physical' : $selectedOrderType;
        $regionTabs = [
            9 => [
                'key' => 'global',
                'title' => 'FiROAM Global',
                'packages' => $globalPackageCards,
                'countries' => $globalCountries->values()->all(),
            ],
            21 => [
                'key' => 'asia',
                'title' => 'FiROAM Asia',
                'packages' => $asiaPackageCards,
                'countries' => $asiaCountries->values()->all(),
            ],
        ];
        $countryMap = [
            9 => $globalCountries->values()->all(),
            21 => $asiaCountries->values()->all(),
        ];
        $initialCountryOptions = $countryMap[$selectedDpId] ?? [];
    @endphp

    <section class="service-section physical-package-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content text-center physical-package-heading" data-aos="fade-right"></div>
                </div>
            </div>
            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>

            <div class="services-data mt-4">
                <div class="physical-package-panel" data-country-map='@json($countryMap)'>
                    <div class="physical-order-tabs-shell">
                        <div class="physical-order-tabs" role="tablist" aria-label="Physical SIM order tabs">
                            @foreach ($orderTabs as $orderType => $orderData)
                                <button type="button"
                                    class="physical-order-tab {{ $displayOrderType === $orderType ? 'active' : '' }}"
                                    data-order-tab="{{ $orderType }}">
                                    {{ $orderData['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($orderTabs as $orderType => $orderData)
                        <div class="physical-order-pane {{ $displayOrderType === $orderType ? 'active' : '' }}"
                            data-order-pane="{{ $orderType }}">
                            <div class="content text-center physical-region-heading" data-aos="fade-right">
                                <h2>CHOOSE SIM REGION</h2>
                            </div>
                            <form method="get" action="{{ route('physical.roamsearch') }}" data-request-loader
                                class="physical-search-form">
                                <input type="hidden" name="type" value="{{ $orderType }}">
                                <input type="hidden" name="dp_id" value="{{ $selectedDpId }}" class="js-dp-id">

                                <div class="physical-region-tabs" role="tablist" aria-label="Physical SIM region tabs">
                                    @foreach ($regionTabs as $dpId => $region)
                                        <button type="button"
                                            class="physical-region-tab {{ $selectedDpId === $dpId ? 'active' : '' }}"
                                            data-region-tab="{{ $dpId }}">
                                            {{ $region['title'] }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="physical-search-card">
                                    <div class="physical-region-current js-region-current">
                                        {{ $regionTabs[$selectedDpId]['title'] ?? 'FiROAM Global' }}
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0 physical-search-form-group">
                                                <h4>Shop for the best Physical SIM offers</h4>
                                                <select class="select2_design form-control js-country-select"
                                                    multiple="multiple" name="countryname[]"
                                                    data-placeholder="Search for destination..."
                                                    aria-label="Search for destination">
                                                    <option value=""></option>
                                                    @foreach ($initialCountryOptions as $countryname)
                                                        <option value="{{ $countryname }}">{{ $countryname }}</option>
                                                    @endforeach
                                                </select>
                                                @error('countryname[]')
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

                            <div class="physical-package-stage">
                                <div class="physical-package-stage__title">
                                    <h3>Package Plan</h3>
                                </div>
                                <div class="physical-region-panels physical-package-panels">
                                    @foreach ($regionTabs as $dpId => $region)
                                        @php
                                            $groupValidCount = 0;
                                            $packageGridKey = $orderType . '-' . $region['key'];
                                        @endphp
                                        <div class="physical-region-panel {{ $selectedDpId === $dpId ? 'active' : '' }}"
                                            data-region-panel="{{ $dpId }}"
                                            style="display: {{ $selectedDpId === $dpId ? 'block' : 'none' }};">
                                            <div class="physical-package-grid" id="package-list-{{ $packageGridKey }}"
                                                data-package-grid="{{ $packageGridKey }}">
                                                @foreach ($region['packages'] as $card)
                                                    @php
                                                        $package = $card['package'] ?? null;
                                                        $roam = $card['roam'] ?? null;
                                                        $lowestPrice = $card['lowest_price'] ?? null;
                                                    @endphp

                                                    @if ($package && $roam && $lowestPrice)
                                                        @php $groupValidCount++; @endphp
                                                        <div class="package-card"
                                                            data-package-card="{{ $packageGridKey }}">
                                                            <div class="service-box physical-service-box">
                                                                <figure class="img img2 mb-3">
                                                                    <img src="{{ file_exists(public_path('storage/upload/roam/' . $roam->image)) ? asset('storage/upload/roam/' . $roam->image) : asset($roam->image ?? 'assets/images/package.jpg') }}"
                                                                        alt="{{ $package->country_name }}"
                                                                        class="img-fluid">
                                                                </figure>
                                                                <div class="content">
                                                                    <h4>{{ $package->country_name }}</h4>
                                                                    <p class="text-size-16">From
                                                                        {{ number_format($lowestPrice) }} MMK</p>
                                                                    <a href="{{ route('physical.roampackageview', ['id' => $package->sku_id, 'list_view' => '1', 'sim_type' => $orderType, 'dp_id' => $dpId]) }}"
                                                                        class="more">View Offer</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            @if ($groupValidCount === 0)
                                                <div class="physical-empty-state">
                                                    <strong>No packages available yet.</strong>
                                                    <p>The selected DP group does not have any package cards ready to show.
                                                    </p>
                                                </div>
                                            @elseif ($groupValidCount > 6)
                                                <div class="text-center mt-4">
                                                    <button id="showMoreBtn-{{ $packageGridKey }}"
                                                        class="btn btn-primary px-4 py-2"
                                                        data-show-more="{{ $packageGridKey }}">Show All</button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
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
    <!-- need more help? -->
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
                            <img src="{{ asset('assets/images/need-sales-icon.png') }}" alt=""
                                class="img-fluid">
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

    <style>
        .physical-package-heading h2,
        .physical-region-heading h2 {
            font-size: clamp(1rem, 1.8vw, 1.5rem);
            line-height: 1.05;
            color: #251f5a;
        }

        .physical-region-heading {
            margin-bottom: 1rem;
        }

        .physical-package-panel {
            position: relative;
            z-index: 1;
        }

        .physical-package-section .services-data {
            clear: both;
        }

        .physical-package-panels {
            margin-top: 2rem;
            padding-top: 0;
        }

        .physical-package-stage {
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(18, 58, 134, 0.08);
        }

        .physical-package-stage__title {
            margin-bottom: 18px;
            text-align: center;
        }

        .physical-package-stage__title h3 {
            margin-bottom: 0;
            color: #251f5a;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .physical-order-tabs-shell,
        .physical-region-tabs,
        .physical-order-pane {
            border-radius: 4px;
            border: 1px solid rgba(18, 58, 134, 0.12);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 249, 255, 0.96));
            box-shadow: 0 18px 42px rgba(10, 36, 104, 0.08);
        }

        .physical-order-tabs-shell {
            width: min(100%, 390px);
            margin: 0.85rem 0 1.1rem 0.15rem;
            padding: 0;
            background: transparent;
            box-shadow: none;
            border: 0;
        }

        .physical-order-tabs,
        .physical-region-tabs {
            display: flex;
            gap: 0.95rem;
            align-items: stretch;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .physical-order-tab {
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

        .physical-order-tab.active,
        .physical-region-tab.active {
            background: linear-gradient(135deg, #123a86, #0f4ea8);
            color: #ffffff;
            border: 1px solid transparent;
            outline: none;
            box-shadow: 0 14px 30px rgba(10, 36, 104, 0.2);
        }

        .physical-order-tab:focus,
        .physical-order-tab:focus-visible {
            outline: none;
        }

        .physical-order-pane {
            display: none;
            padding: 0.25rem 0 0;
            margin-bottom: 1.25rem;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        .physical-order-pane.active {
            display: block;
        }

        .physical-region-tabs {
            width: min(100%, 440px);
            margin: 0 auto 0.78rem;
            padding: 0.12rem;
            gap: 0.35rem;
            background: linear-gradient(180deg, rgba(240, 244, 252, 0.98), rgba(228, 235, 248, 0.98));
        }

        .physical-region-tab {
            min-height: 54px;
            padding: 0.7rem 0.55rem;
            border-radius: 12px;
            font-size: 0.85rem;
            flex: 1 1 0;
            border: 0;
            background: transparent;
        }

        .physical-search-card {
            padding: 1rem 1rem 0.95rem;
            background: #ffffff;
            border: 1px solid rgba(18, 58, 134, 0.12);
            border-radius: 4px;
            box-shadow: 0 6px 14px rgba(10, 36, 104, 0.05);
        }

        .physical-search-form-group h4,
        .physical-package-group__header h3 {
            margin-bottom: 0.6rem;
            color: #251f5a;
            font-size: 1.3rem;
        }

        .physical-region-current {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #123a86;
            letter-spacing: 0.01em;
        }

        .physical-search-card .select2-container,
        .physical-search-card .select2-container--bootstrap4 {
            width: 100% !important;
            display: block !important;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection {
            width: 100% !important;
            min-height: 60px;
            height: 60px !important;
            box-sizing: border-box;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 60px;
            height: 60px !important;
            padding: 0.5rem 0.75rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            width: 100%;
            height: 100%;
            padding-left: 0;
            margin-top: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            max-width: 100%;
            margin: 0.1rem 0 0;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-search--inline,
        .physical-search-card .select2-container--bootstrap4 .select2-search__field {
            width: auto !important;
            max-width: none !important;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-search--inline {
            display: inline-flex;
            align-items: center;
            float: none;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-search__field {
            min-width: 90px;
            margin-top: 0;
            font-size: 1rem;
            line-height: 1.3;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection__placeholder {
            font-size: 1rem;
            line-height: 1.3;
        }

        .physical-search-card .button_text {
            min-height: 60px;
            padding: 0 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .physical-package-group__header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(18, 58, 134, 0.08);
        }

        .physical-region-panel {
            display: none;
            margin-top: 0.75rem;
        }

        .physical-region-panel.active {
            display: block;
        }

        .physical-package-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
            column-gap: 1rem;
            row-gap: 1.1rem;
            margin-top: 1rem;
        }

        .physical-package-grid .package-card {
            width: 100%;
            min-width: 0;
        }

        .physical-service-box {
            height: 100%;
        }

        .physical-service-box .img {
            margin-bottom: 0.65rem !important;
        }

        .physical-service-box .img img {
            width: 100%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
        }

        .physical-service-box .content {
            padding-bottom: 0.35rem;
        }

        .physical-service-box .content h4 {
            margin-bottom: 0.2rem;
        }

        .physical-service-box .content .text-size-16 {
            margin-bottom: 0.55rem;
        }

        .physical-service-box .content .more {
            margin-top: 0.15rem;
        }

        .physical-empty-state {
            padding: 1rem 1.15rem;
            border-radius: 18px;
            background: rgba(247, 249, 253, 0.9);
            border: 1px dashed rgba(18, 58, 134, 0.18);
            color: #3c4a63;
        }

        .physical-empty-state strong {
            display: block;
            margin-bottom: 0.35rem;
        }

        @media (min-width: 992px) and (max-width: 1199.98px) {
            .physical-package-section {
                padding-top: 1.75rem;
            }

            .physical-order-tabs-shell {
                width: 100%;
                margin: 0.8rem 0 1rem 0;
            }

            .physical-order-tabs {
                gap: 0.75rem;
            }

            .physical-order-tab {
                flex-basis: 144px;
                min-height: 56px;
                font-size: 0.98rem;
            }

            .physical-region-tabs {
                width: 100%;
                gap: 0.45rem;
            }

            .physical-region-tab {
                min-height: 50px;
                font-size: 0.8rem;
            }

            .physical-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 0.9rem;
                row-gap: 0.9rem;
            }

            .physical-service-box .img {
                margin-bottom: 0.55rem !important;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .physical-package-section {
                padding-top: 1.5rem;
            }

            .physical-order-tabs-shell,
            .physical-region-tabs {
                width: 100%;
                gap: 0.65rem;
            }

            .physical-order-tab {
                min-height: 56px;
                flex-basis: 150px;
                font-size: 0.9rem;
            }

            .physical-region-tab {
                min-height: 48px;
                font-size: 0.8rem;
            }

            .physical-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 0.9rem;
                row-gap: 0.9rem;
            }

            .physical-service-box .img {
                margin-bottom: 0.55rem !important;
            }

            .physical-service-box .content h4 {
                font-size: 1.05rem;
            }
        }

        @media (min-width: 576px) and (max-width: 767.98px) {
            .physical-package-section {
                padding-top: 1.25rem;
            }

            .physical-order-tabs-shell {
                width: 100%;
                margin: 0.7rem 0 1rem 0;
            }

            .physical-order-tabs,
            .physical-region-tabs {
                gap: 0.55rem;
            }

            .physical-order-tab {
                flex: 1 1 0;
                min-height: 52px;
                padding: 0.55rem 0.7rem;
                font-size: 0.88rem;
            }

            .physical-region-tabs {
                width: 100%;
            }

            .physical-region-tab {
                min-height: 48px;
                padding: 0.45rem 0.6rem;
                border-radius: 8px;
                font-size: 0.74rem;
            }

            .physical-order-pane {
                padding: 0.2rem 0 0;
            }

            .physical-search-card {
                padding: 0.9rem 0.9rem 0.8rem;
            }

            .physical-search-card .button_text {
                width: 100%;
            }

            .physical-package-grid {
                grid-template-columns: 1fr;
                row-gap: 0.85rem;
            }

            .physical-service-box .img {
                margin-bottom: 0.5rem !important;
            }
        }

        @media (max-width: 575.98px) {
            .physical-package-section {
                padding-top: 1.1rem;
            }

            .physical-order-tabs-shell {
                width: 100%;
                margin: 0 0 1.25rem;
            }

            .physical-order-tabs,
            .physical-region-tabs {
                gap: 0.6rem;
            }

            .physical-order-tab {
                min-height: 52px;
                padding: 0.55rem 0.75rem;
                border-radius: 4px;
                flex: 1 1 0;
                font-size: 0.88rem;
            }

            .physical-region-tab {
                min-height: 48px;
                padding: 0.45rem 0.6rem;
                border-radius: 10px;
                font-size: 0.72rem;
            }

            .physical-order-pane {
                padding: 0.2rem 0 0;
            }

            .physical-package-stage {
                margin-top: 1.15rem;
                padding-top: 0.35rem;
            }

            .physical-search-form-group,
            .physical-package-group__header {
                flex-direction: column;
                align-items: flex-start;
            }

            .physical-region-current {
                margin-bottom: 0.4rem;
                font-size: 0.86rem;
            }

            .physical-search-card .button_text {
                width: 100%;
            }

            .physical-package-grid {
                display: block;
            }

            .physical-service-box .img {
                margin-bottom: 0.5rem !important;
            }

            .physical-service-box .content .text-size-16 {
                margin-bottom: 0.45rem;
            }

            .physical-service-box .content h4 {
                font-size: 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const orderTabs = document.querySelectorAll('[data-order-tab]');
            const orderPanes = document.querySelectorAll('[data-order-pane]');
            const countryMapContainer = document.querySelector('[data-country-map]');
            let countryMap = {};

            if (countryMapContainer) {
                try {
                    countryMap = JSON.parse(countryMapContainer.getAttribute('data-country-map') || '{}');
                } catch (error) {
                    countryMap = {};
                }
            }

            function updateUrlParam(name, value) {
                const url = new URL(window.location.href);
                url.searchParams.set(name, value);
                window.history.replaceState({}, '', url.toString());
            }

            function renderCountries(select, countries) {
                if (!select) {
                    return;
                }

                const selectedValues = Array.from(select.selectedOptions || []).map((option) => option.value);
                const validSelected = selectedValues.filter((value) => countries.includes(value));

                select.innerHTML = '<option value=""></option>';
                countries.forEach((country) => {
                    const option = document.createElement('option');
                    option.value = country;
                    option.textContent = country;
                    if (validSelected.includes(country)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    window.jQuery(select).trigger('change.select2');
                } else {
                    select.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            }

            function activateOrderType(orderType) {
                orderTabs.forEach((tab) => {
                    tab.classList.toggle('active', tab.getAttribute('data-order-tab') === String(
                        orderType));
                });

                orderPanes.forEach((pane) => {
                    pane.classList.toggle('active', pane.getAttribute('data-order-pane') === String(
                        orderType));
                });

                updateUrlParam('type', orderType);
            }

            function activateRegion(dpId) {
                orderPanes.forEach((pane) => {
                    pane.querySelectorAll('[data-region-tab]').forEach((tab) => {
                        tab.classList.toggle('active', tab.getAttribute('data-region-tab') ===
                            String(dpId));
                    });

                    pane.querySelectorAll('[data-region-panel]').forEach((panel) => {
                        const isActive = panel.getAttribute('data-region-panel') === String(dpId);
                        panel.classList.toggle('active', isActive);
                        panel.style.display = isActive ? 'block' : 'none';
                    });

                    const dpInput = pane.querySelector('.js-dp-id');
                    if (dpInput) {
                        dpInput.value = dpId;
                    }

                    const select = pane.querySelector('.js-country-select');
                    const countries = countryMap[String(dpId)] || countryMap[dpId] || [];
                    renderCountries(select, countries);

                    const regionCurrent = pane.querySelector('.js-region-current');
                    const regionTitle = pane.querySelector(`[data-region-tab="${dpId}"]`)?.textContent
                        ?.trim() ||
                        'FiROAM Global';
                    if (regionCurrent) {
                        regionCurrent.textContent = regionTitle;
                    }
                });

                updateUrlParam('dp_id', dpId);
            }

            orderTabs.forEach((tab) => {
                tab.addEventListener('click', function() {
                    activateOrderType(this.getAttribute('data-order-tab'));
                });
            });

            document.querySelectorAll('[data-region-tab]').forEach((tab) => {
                tab.addEventListener('click', function() {
                    activateRegion(this.getAttribute('data-region-tab'));
                });
            });

            activateOrderType(document.querySelector('[data-order-tab].active')?.getAttribute('data-order-tab') ||
                'recharge_physical');
            activateRegion(document.querySelector('[data-region-tab].active')?.getAttribute('data-region-tab') ||
                '9');

            document.querySelectorAll('[data-package-grid]').forEach((grid) => {
                const gridKey = grid.getAttribute('data-package-grid');
                const items = Array.from(grid.querySelectorAll('.package-card'));
                const showMoreBtn = document.getElementById(`showMoreBtn-${gridKey}`);
                const visibleCount = 6;

                if (!items.length) {
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

                if (!showMoreBtn || items.length <= visibleCount) {
                    if (showMoreBtn) {
                        showMoreBtn.style.display = 'none';
                    }
                    return;
                }

                showMoreBtn.addEventListener('click', function() {
                    items.forEach((item) => {
                        item.style.display = 'block';
                    });

                    this.style.display = 'none';
                });
            });
        });
    </script>
@endsection
