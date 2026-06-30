@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <!-- Sub-Banner -->
    @php
        $file = get_banner('joytel_physical');
        $image = $file !== null ? 'banner/' . $file : 'assets/images/default-banner.png';
        $banner = \App\Models\Banner::where('banner_type', 'joytel_physical')->first();
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
                class="mb-0 text-size-16">Physical</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">{{ $settings['joytel_title']->value ?? 'Joytel' }}</span>
        </div>
    </div>
    @php
        $orderTabs = $orderTabs ?? [
            'recharge_physical' => ['label' => 'Recharge'],
        ];
        $selectedSimType = $selectedSimType ?? 'recharge_physical';
        $packages = $packages->values();
    @endphp

    <section class="service-section physical-package-section">
        <div class="container">
            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>

            <div class="services-data mt-4">
                <div class="physical-package-panel">
                    <div class="physical-order-tabs" role="tablist" aria-label="physical order tabs">
                        @foreach ($orderTabs as $orderType => $orderData)
                            <button type="button"
                                class="physical-order-tab {{ $selectedSimType === $orderType ? 'active' : '' }}"
                                data-order-tab="{{ $orderType }}">
                                {{ $orderData['label'] }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($orderTabs as $orderType => $orderData)
                        <div class="physical-order-pane {{ $selectedSimType === $orderType ? 'active' : '' }}"
                            data-order-pane="{{ $orderType }}">
                            <form method="get" action="{{ route('physical.search') }}" class="physical-search-form">
                                <input type="hidden" name="type" value="{{ $orderType }}">
                                <div class="physical-search-card">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0 physical-search-form-group">
                                                <h4>Shop for the best Recharge offers</h4>
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

                            <div class="physical-package-stage">
                                <div class="physical-package-stage__title">
                                    <h3>Package Plan</h3>
                                </div>

                                @if ($packages->isEmpty())
                                    <div class="physical-empty-state">
                                        <p>Packages are temporarily not available.</p>
                                    </div>
                                @else
                                    <div class="physical-package-grid" id="package-list-{{ $orderType }}"
                                        data-package-grid="{{ $orderType }}">
                                        @foreach ($packages as $index => $package)
                                            <div class="package-card {{ $index >= 6 ? 'hidden' : '' }}"
                                                data-package-card="{{ $orderType }}">
                                                <div class="service-box physical-service-box">
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
                                                        <h4>{{ $package->product_name }}</h4>
                                                        @php
                                                            $lowest_price = App\Models\JoytelPhysical::where(
                                                                'product_name',
                                                                $package->product_name,
                                                            )
                                                                ->where('status', 1)
                                                                ->get()
                                                                ->map(function ($plan) {
                                                                    $priceList = \App\Models\PriceList::where(
                                                                        'product_code',
                                                                        $plan->code ?? null,
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
                                                        <a href="{{ route('joytel.physical.packageview', ['id' => $package->id, 'sim_type' => $orderType]) }}"
                                                            class="more">View Offer</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($packages->count() > 6)
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
    <!-- need more help? -->
    <x-frontend.section-item :section="$section" />

    <style>
        .physical-package-section {
            position: relative;
            overflow: hidden;
        }

        .physical-package-panel {
            position: relative;
            z-index: 1;
        }

        .physical-order-tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: stretch;
            justify-content: flex-start;
            width: min(100%, 390px);
            margin: 0.85rem 0 1.1rem 0.15rem;
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

        .physical-order-tab.active {
            background: linear-gradient(135deg, #123a86, #0f4ea8);
            color: #fff;
            border: 1px solid transparent;
            outline: none;
            box-shadow: 0 14px 30px rgba(10, 36, 104, 0.2);
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

        .physical-search-card {
            padding: 1rem 1rem 0.95rem;
            background: #ffffff;
            border: 1px solid rgba(18, 58, 134, 0.12);
            border-radius: 4px;
            box-shadow: 0 6px 14px rgba(10, 36, 104, 0.05);
        }

        .physical-search-form-group h4,
        .physical-package-stage__title h3 {
            margin-bottom: 0.6rem;
            color: #251f5a;
            font-size: 1.3rem;
        }

        .physical-package-stage__title h3 {
            font-weight: 800;
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

        .physical-package-stage {
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(18, 58, 134, 0.08);
        }

        .physical-package-stage__title {
            margin-bottom: 18px;
            text-align: center;
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

        .hidden {
            display: none;
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
            padding: 2rem 1rem 1.5rem;
            text-align: center;
            color: #5e6473;
        }

        .physical-empty-state p {
            margin-bottom: 0;
            font-size: 18px;
        }

        @media (max-width: 1199.98px) {
            .physical-package-section .container {
                max-width: 100%;
            }

            .physical-order-tabs {
                width: 100%;
                margin-left: 0;
            }

            .physical-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 0.9rem;
                row-gap: 0.9rem;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .physical-package-section {
                padding-top: 0.5rem;
            }

            .physical-package-panel {
                padding: 0 0.25rem;
            }

            .physical-order-tabs {
                gap: 0.65rem;
                margin: 0.75rem 0 1rem;
            }

            .physical-order-tab {
                min-height: 56px;
                flex-basis: 150px;
                font-size: 0.9rem;
            }

            .physical-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 0.9rem;
                row-gap: 0.9rem;
            }

            .physical-search-card {
                padding: 0.95rem 0.95rem 0.9rem;
            }

            .physical-search-card .button_text {
                min-width: 220px;
            }

            .physical-package-stage {
                margin-top: 1.25rem;
            }
        }

        @media (max-width: 767.98px) {
            .physical-package-section {
                padding-top: 1.5rem;
            }

            .physical-package-panel {
                padding: 0;
            }

            .physical-package-section .element1,
            .physical-package-section .element2 {
                opacity: 0.35;
                transform: scale(0.8);
            }

            .physical-package-section .services-data {
                margin-top: 1.5rem !important;
            }

            .physical-order-tabs {
                width: 100%;
                margin: 1.1rem 0 1rem 0;
                gap: 0.6rem;
            }

            .physical-order-tab {
                flex: 1 1 100%;
                min-height: 52px;
                padding: 0.55rem 0.75rem;
                font-size: 0.88rem;
            }

            .physical-package-grid {
                grid-template-columns: 1fr;
            }

            .physical-order-pane {
                padding: 0.2rem 0 0;
            }

            .physical-search-card {
                padding: 0.85rem 0.85rem 0.8rem;
                border-radius: 12px;
            }

            .physical-search-form-group h4,
            .physical-package-stage__title h3 {
                font-size: 1.1rem;
            }

            .physical-search-card .select2-container--bootstrap4 .select2-selection,
            .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple,
            .physical-search-card .button_text {
                min-height: 52px;
                height: 52px !important;
            }

            .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple {
                padding: 0.45rem 0.7rem;
            }

            .physical-search-card .button_text {
                width: 100%;
            }

            .physical-package-stage {
                padding-top: 0.35rem;
                margin-top: 1.15rem;
            }

            .physical-empty-state {
                padding: 1.5rem 0.75rem 1rem;
            }

            .physical-empty-state p {
                font-size: 16px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-package-grid]').forEach((grid) => {
                const items = grid.querySelectorAll('.package-card');
                const gridKey = grid.getAttribute('data-package-grid');
                const showMoreBtn = document.querySelector(`[data-show-more="${gridKey}"]`);

                if (!showMoreBtn || items.length === 0) {
                    if (showMoreBtn) {
                        showMoreBtn.style.display = 'none';
                    }
                    return;
                }

                showMoreBtn.addEventListener('click', function() {
                    items.forEach((item) => item.classList.remove('hidden'));
                    this.style.display = 'none';
                });
            });
        });
    </script>
@endsection
