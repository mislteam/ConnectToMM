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
    <!--Contact-->
    <section class="payment-section">
        <div class="container">
            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>
            <div class="form-design tabs-container">
                <ul class="nav" role="tablist">
                    <li><a class="nav-link active" data-toggle="tab" href="#roam-recharge">Recharge</a></li>
                </ul>
                <div class="tab-content mt-4 bg-light">
                    <div role="tabpanel" id="roam-recharge" class="tab-pane active show">
                        <div class="panel-body">
                            <div class="message_content" data-aos="fade-up">
                                <form method="get" action="{{ route('physical.roamsearch') }}">
                                    <div class="physical-search-frame">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="physical-dp-switch-wrap">
                                                    <div class="physical-dp-switch-title">Choose package region</div>
                                                    <div class="physical-dp-switch">
                                                        <label class="dp-pill {{ $selectedDpId === 9 ? 'active' : '' }}">
                                                            <input type="radio" name="dp_id" value="9"
                                                                {{ $selectedDpId === 9 ? 'checked' : '' }}>
                                                            <span class="dp-pill__text">
                                                                <strong>Global Packages</strong>
                                                            </span>
                                                        </label>
                                                        <label class="dp-pill {{ $selectedDpId === 21 ? 'active' : '' }}">
                                                            <input type="radio" name="dp_id" value="21"
                                                                {{ $selectedDpId === 21 ? 'checked' : '' }}>
                                                            <span class="dp-pill__text">
                                                                <strong>Asia Packages</strong>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <!-- type -->
                                                <input type="hidden" name="type" value="recharge_physical">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group mb-0">
                                                            <h4>SIM ICCID Number</h4>
                                                            <input type="text" name="iccid_number" class="form_style"
                                                                placeholder="Enter Your SIM ICCID No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group mb-0">
                                                            <h4>Shop for the best Physical SIM offers</h4>
                                                            <select class="select2_design form-control" multiple="multiple"
                                                                name="countryname[]">
                                                                @foreach ($countrys as $countryname)
                                                                    <option value="{{ $countryname }}">{{ $countryname }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('countryname[]')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="mt-4 text-center">
                                                            <button type="submit" class="button_text">Continue
                                                                Search</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Services section-->
    <section class="service-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content text-center" data-aos="fade-right">
                        <h6>Physical-SIM - {{ $settings['roam_title']->value ?? 'Roam' }}</h6>
                        <h2>Package Plan</h2>
                        <p class="text-size-18">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                            tempor incididuntabore et dolore aliquaQuis ipsum suspe.</p>
                    </div>
                </div>
            </div>
            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>
            <div class="services-data mt-4">
                <div class="form-design tabs-container">
                    <div class="tab-content mt-4 shadow-none p-0">
                        <div role="tabpanel" id="roam-new-physical-package" class="tab-pane active">
                            <div class="panel-body">
                                @php
                                    $globalValidCount = 0;
                                @endphp
                                <div class="package-group" data-package-group="global"
                                    style="display: {{ $selectedDpId === 9 ? 'block' : 'none' }};">
                                    <div class="row" id="package-list-global">
                                        @foreach ($globalSkupackages as $package)
                                            @php
                                                $pkg = App\Models\RoamPhysical::where(
                                                    'sku_id',
                                                    $package->sku_id,
                                                )->first();

                                                $lowestPrice = null;

                                                if ($pkg && !empty($pkg->packages)) {
                                                    $priceMap = $priceList
                                                        ->where('plan', $package->sku_id)
                                                        ->pluck('exchange_rate', 'product_code');

                                                    $lowestPrice = collect($pkg->packages)
                                                        ->filter(fn($p) => ($p['status'] ?? 0) == 1)
                                                        ->map(function ($p) use ($priceMap) {
                                                            $apiCode = $p['apiCode'] ?? ($p['api_code'] ?? null);
                                                            $legacyCode = $p['priceid'] ?? null;
                                                            $rate =
                                                                $apiCode !== null && isset($priceMap[$apiCode])
                                                                    ? $priceMap[$apiCode]
                                                                    : ($legacyCode !== null &&
                                                                    isset($priceMap[$legacyCode])
                                                                        ? $priceMap[$legacyCode]
                                                                        : null);
                                                            if ($rate === null) {
                                                                return null;
                                                            }
                                                            $portalPrice =
                                                                ($p['price'] ?? 0) + ($p['openCardFee'] ?? 0);
                                                            return $portalPrice * $rate;
                                                        })
                                                        ->filter()
                                                        ->min();
                                                }
                                            @endphp

                                            @if (!empty($pkg) && $lowestPrice)
                                                @php $globalValidCount++; @endphp
                                                <div class="col-lg-4 col-md-4 col-sm-12 col-12 package-card"
                                                    data-package-card="global">
                                                    <div class="service-box">
                                                        <figure class="img img2 mb-3">
                                                            <img src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}"
                                                                alt="" class="img-fluid">
                                                        </figure>
                                                        <div class="content">
                                                            <h4>{{ $package->country_name }}</h4>
                                                            @if ($lowestPrice)
                                                                <p class="text-size-16">From
                                                                    {{ number_format($lowestPrice) }} MMK</p>
                                                            @else
                                                                <p class="text-size-16 text-danger">Not available</p>
                                                            @endif
                                                            <a href="{{ route('physical.roampackageview', ['id' => $package->sku_id, 'list_view' => '1']) }}"
                                                                class="more">View Offer</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if ($globalValidCount > 6)
                                        <div class="text-center mt-4">
                                            <button id="showMoreBtn-global" class="btn btn-primary px-4 py-2"
                                                data-show-more="global">Show More</button>
                                        </div>
                                    @endif
                                </div>

                                @php
                                    $asiaValidCount = 0;
                                @endphp
                                <div class="package-group" data-package-group="asia"
                                    style="display: {{ $selectedDpId === 21 ? 'block' : 'none' }};">
                                    <div class="row" id="package-list-asia">
                                        @foreach ($asiaSkupackages as $package)
                                            @php
                                                $pkg = App\Models\RoamPhysical::where(
                                                    'sku_id',
                                                    $package->sku_id,
                                                )->first();

                                                $lowestPrice = null;

                                                if ($pkg && !empty($pkg->packages)) {
                                                    $priceMap = $priceList
                                                        ->where('plan', $package->sku_id)
                                                        ->pluck('exchange_rate', 'product_code');

                                                    $lowestPrice = collect($pkg->packages)
                                                        ->filter(fn($p) => ($p['status'] ?? 0) == 1)
                                                        ->map(function ($p) use ($priceMap) {
                                                            $apiCode = $p['apiCode'] ?? ($p['api_code'] ?? null);
                                                            $legacyCode = $p['priceid'] ?? null;
                                                            $rate =
                                                                $apiCode !== null && isset($priceMap[$apiCode])
                                                                    ? $priceMap[$apiCode]
                                                                    : ($legacyCode !== null &&
                                                                    isset($priceMap[$legacyCode])
                                                                        ? $priceMap[$legacyCode]
                                                                        : null);
                                                            if ($rate === null) {
                                                                return null;
                                                            }
                                                            $portalPrice =
                                                                ($p['price'] ?? 0) + ($p['openCardFee'] ?? 0);
                                                            return $portalPrice * $rate;
                                                        })
                                                        ->filter()
                                                        ->min();
                                                }
                                            @endphp

                                            @if (!empty($pkg) && $lowestPrice)
                                                @php $asiaValidCount++; @endphp
                                                <div class="col-lg-4 col-md-4 col-sm-12 col-12 package-card"
                                                    data-package-card="asia">
                                                    <div class="service-box">
                                                        <figure class="img img2 mb-3">
                                                            <img src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}"
                                                                alt="" class="img-fluid">
                                                        </figure>
                                                        <div class="content">
                                                            <h4>{{ $package->country_name }}</h4>
                                                            @if ($lowestPrice)
                                                                <p class="text-size-16">From
                                                                    {{ number_format($lowestPrice) }} MMK</p>
                                                            @else
                                                                <p class="text-size-16 text-danger">Not available</p>
                                                            @endif
                                                            <a href="{{ route('physical.roampackageview', ['id' => $package->sku_id, 'list_view' => '1']) }}"
                                                                class="more">View Offer</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if ($asiaValidCount > 6)
                                        <div class="text-center mt-4">
                                            <button id="showMoreBtn-asia" class="btn btn-primary px-4 py-2"
                                                data-show-more="asia">Show More</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <figure class="element2 mb-0">
                            <img src="{{ asset('assets/images/what-we-do-icon-2.png') }}" class="img-fluid"
                                alt="">
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
        .physical-dp-switch-wrap {
            margin-bottom: 1.5rem;
            padding: 1.1rem 1rem 1rem;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(241, 245, 255, 0.98));
            border: 1px solid rgba(15, 54, 143, 0.12);
            box-shadow: 0 12px 28px rgba(10, 36, 104, 0.08);
        }

        .physical-dp-switch-title {
            margin-bottom: 0.85rem;
            text-align: center;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #5d6b82;
        }

        .physical-dp-switch {
            display: flex;
            align-items: stretch;
            gap: 0;
            width: min(100%, 760px);
            margin: 0 auto;
            padding: 0.35rem;
            border-radius: 22px;
            background: rgba(232, 238, 251, 0.78);
            border: 1px solid rgba(18, 58, 134, 0.1);
        }

        .physical-dp-switch .dp-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            min-width: 0;
            min-height: 66px;
            padding: 0.9rem 1rem;
            margin: 0;
            border-radius: 18px;
            border: 0;
            background: transparent;
            color: #123a86;
            cursor: pointer;
            transition: all 0.22s ease;
            box-shadow: none;
            position: relative;
            overflow: hidden;
            flex: 1 1 0;
        }

        .physical-dp-switch .dp-pill:hover {
            transform: translateY(-1px);
        }

        .physical-dp-switch .dp-pill input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .physical-dp-switch .dp-pill__text {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.15;
            text-align: center;
            width: 100%;
        }

        .physical-dp-switch .dp-pill__text strong {
            font-size: 1.02rem;
            font-weight: 800;
        }

        .physical-dp-switch .dp-pill.active {
            background: linear-gradient(135deg, #123a86, #0f4ea8);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(10, 36, 104, 0.22);
        }

        .physical-dp-switch .dp-pill.active .dp-pill__text strong {
            color: #ffffff;
        }

        .physical-dp-switch .dp-pill:focus-within {
            outline: 3px solid rgba(18, 58, 134, 0.18);
            outline-offset: 2px;
        }

        .physical-search-form .physical-search-frame {
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .physical-search-card .select2-container {
            width: 100% !important;
            display: block;
        }

        .physical-search-card .select2-container--bootstrap4 {
            width: 100% !important;
            display: block !important;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection {
            width: 100% !important;
            min-height: 60px;
            box-sizing: border-box;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 60px;
            padding: 0.5rem 0.75rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            width: 100%;
            padding-left: 0;
            margin-top: 0;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            max-width: 100%;
            margin-top: 0.2rem;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-search--inline,
        .physical-search-card .select2-container--bootstrap4 .select2-search__field {
            width: 100% !important;
            max-width: 100% !important;
        }

        .physical-search-card .select2-container--bootstrap4 .select2-search__field {
            margin-top: 0.15rem;
        }

        @media (max-width: 575.98px) {
            .physical-search-hero {
                padding: 0.75rem;
                border-radius: 22px;
            }

            .physical-search-copy h2 {
                font-size: 1.65rem;
                line-height: 1.1;
            }

            .physical-search-copy p {
                font-size: 0.95rem;
                line-height: 1.55;
                margin-bottom: 1rem;
            }

            .physical-search-highlights {
                display: none;
            }

            .physical-search-card {
                padding: 0.75rem;
                border-radius: 22px;
            }

            .physical-search-card__header {
                padding: 0.25rem 0.15rem 0.85rem;
            }

            .physical-search-card__header h3 {
                font-size: 1.15rem;
            }

            .physical-search-card__header p {
                font-size: 0.9rem;
            }

            .physical-dp-switch-wrap {
                padding: 0.85rem 0.75rem 0.8rem;
                border-radius: 18px;
                margin-bottom: 1rem;
            }

            .physical-dp-switch-title {
                font-size: 0.78rem;
                margin-bottom: 0.7rem;
            }

            .physical-dp-switch {
                flex-direction: column;
                width: 100%;
                gap: 0.55rem;
            }

            .physical-dp-switch .dp-pill {
                width: 100%;
                min-height: 58px;
                padding: 0.85rem 0.95rem;
                border-radius: 16px;
            }

            .physical-dp-switch .dp-pill__text {
                align-items: flex-start;
            }

            .physical-dp-switch .dp-pill__text strong {
                font-size: 0.95rem;
            }

            .physical-dp-switch .dp-pill__text small {
                font-size: 0.74rem;
            }

            .physical-input-group {
                margin-bottom: 0.95rem;
            }

            .physical-input-label-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.2rem;
                margin-bottom: 0.45rem;
            }

            .physical-input-label-row h4 {
                font-size: 0.98rem;
            }

            .physical-input-label-row span {
                font-size: 0.76rem;
            }

            .physical-search-card .form_style {
                min-height: 54px;
                padding-left: 0.95rem;
                padding-right: 0.95rem;
            }

            .physical-search-card .select2-container--bootstrap4 .select2-selection--multiple {
                min-height: 54px;
                padding: 0.35rem 0.6rem;
            }

            .physical-search-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 0.7rem;
                margin-top: 0.9rem;
                padding-top: 0.9rem;
            }

            .physical-search-note {
                font-size: 0.82rem;
                text-align: center;
            }

            .physical-search-card .button_text {
                width: 100%;
                min-width: 0;
                padding: 14px 18px;
                font-size: 15px;
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const groupRadios = document.querySelectorAll('input[name="dp_id"]');

            groupRadios.forEach((radio) => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.physical-dp-switch label').forEach((label) => {
                        label.classList.remove('active');
                    });
                    this.closest('label')?.classList.add('active');

                    const url = new URL(window.location.href);
                    url.searchParams.set('dp_id', this.value);
                    window.location.href = url.toString();
                });
            });

            document.querySelectorAll('[data-package-group]').forEach((group) => {
                const groupKey = group.getAttribute('data-package-group');
                const items = group.querySelectorAll('.package-card');
                const showMoreBtn = document.getElementById(`showMoreBtn-${groupKey}`);
                let visibleCount = 6;

                if (!showMoreBtn || items.length === 0) {
                    if (showMoreBtn) showMoreBtn.style.display = 'none';
                    return;
                }

                items.forEach((item, index) => {
                    if (index >= visibleCount) {
                        item.style.display = 'none';
                    }
                });

                if (items.length <= visibleCount) {
                    showMoreBtn.style.display = 'none';
                }

                showMoreBtn.addEventListener('click', function() {
                    let revealed = 0;

                    items.forEach((item, index) => {
                        if (index >= visibleCount && revealed < 6) {
                            item.style.display = 'block';
                            revealed++;
                        }
                    });

                    visibleCount += 6;

                    if (visibleCount >= items.length) {
                        this.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection
