@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    <!-- Sub-Banner -->
    <div class="sub-banner">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="./assets/images/homebanner-bgshape.png" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>E-SIM - Joytel</h1>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                                labore et dolore magna aliqua.</p>
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
            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>
            <div class="services-data mt-4">
                <div class="row">
                    @forelse ($packages as $index => $package)
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
                                    <p class="text-size-16">From
                                        {{ number_format($lowest_price) }}
                                        MMK
                                    </p>
                                    <a href="{{ route('joytel.packageview', $package->id) }}" class="more">View
                                        Offer</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center w-100">Packages are temporarily unavailable.</p>
                    @endforelse
                </div>
            </div>
            <figure class="element2 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-2.png') }}" class="img-fluid" alt="">
            </figure>
        </div>
    </section>
@endsection
