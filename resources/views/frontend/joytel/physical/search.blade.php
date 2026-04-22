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
                class="mb-0 text-size-16 box_span">Joytel</span>
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
                    <li><a class="nav-link active" data-toggle="tab" href="#joytel-new-esim"> Recharge</a></li>
                </ul>
                <div class="tab-content mt-4 bg-light">
                    <div role="tabpanel" id="joytel-new-esim" class="tab-pane active">
                        <div class="panel-body">
                            <div class="message_content" data-aos="fade-up">
                                <form method="get" action="{{ route('physical.search') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <h4>Shop for the best Recharge offers:</h4>
                                                <select class="select2_design form-control" multiple="multiple"
                                                    name="locations[]">
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
                        <h6>Physical - Joytel</h6>
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
                <div class="row">
                    @foreach ($packages as $package)
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="service-box">
                                <figure class="img img2 mb-3">
                                    @if ($package->photo === null || empty($package->photo))
                                        <img src="{{ asset('assets/images/default_sim_image.png') }}" alt="default sim"
                                            class="img-fluid">
                                    @else
                                        <img src="{{ asset('sim/' . $package->photo[0]) }}" alt="data sim"
                                            class="img-fluid">
                                    @endif
                                </figure>
                                <div class="content">
                                    <h4>{{ $package->product_name }}</h4>
                                    @php
                                        $lowest_price = collect($package->plan ?? [])
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
                                    <p class="text-size-16">From
                                        {{ number_format($lowest_price) }}
                                        MMK
                                    </p>
                                    <a href="{{ route('joytel.packageview', $package->id) }}" class="more">View Offer</a>
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
    <x-frontend.section-item :section="$section" />
@endsection
