@extends('frontend.layouts.index')
@section('title', 'Roam E-SIM Package')
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
                            <h1>E-SIM - FiROAM</h1>
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
                class="mb-0 text-size-16 box_span">FiROAM</span>
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
                    @forelse ($packages as $package)
                        @php
                            $pkg = App\Models\Roam::where('sku_id', $package->sku_id)->first();

                        @endphp
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12 mb-4">
                            <div class="service-box">
                                <figure class="img img2 mb-3">
                                    <img src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}"
                                        alt="{{ $package->country_name ?? 'Package' }}" class="img-fluid">
                                </figure>
                                <div class="content">
                                    <h4>{{ $package->country_name ?? 'Unnamed Package' }}</h4>
                                    @php
                                        $itemRoam = App\Models\Roam::where('sku_id', $package->sku_id)->first();
                                        $itemPriceList = App\Models\PriceList::where('plan', $package->sku_id)
                                            ->where('dp_status', 0)
                                            ->first();

                                        $lowestPrice = null;

                                        if ($itemRoam && !empty($itemRoam->packages)) {
                                            $priceMap = App\Models\PriceList::where('plan', $package->sku_id)
                                                ->where('dp_status', 0)
                                                ->pluck('exchange_rate', 'product_code');

                                            $lowestPrice = collect($itemRoam->packages)
                                                ->filter(fn($pkg) => isset($pkg['priceid']) && $pkg['status'] == 1)
                                                ->map(function ($pkg) use ($priceMap) {
                                                    if (!isset($priceMap[$pkg['priceid']])) {
                                                        return null;
                                                    }
                                                    $portalPrice = ($pkg['price'] ?? 0) + ($pkg['openCardFee'] ?? 0);
                                                    return $portalPrice * $priceMap[$pkg['priceid']];
                                                })
                                                ->filter()
                                                ->min();
                                        }
                                    @endphp
                                    @if ($lowestPrice)
                                        <p class="text-size-16">From {{ number_format($lowestPrice) }} MMK</p>
                                    @else
                                        <p class="text-size-16 text-danger">Not available</p>
                                    @endif
                                    <a href="{{ route('esim.roampackageview', ['id' => $package->sku_id]) }}"
                                        class="more">View Offer</a>
                                </div>
                            </div>
                        </div>

                    @empty
                        <div class="col-12">
                            <p>No packages found for the selected countries.</p>
                        </div>
                    @endforelse

                </div>
            </div>
            <figure class="element2 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-2.png') }}" class="img-fluid" alt="">
            </figure>
        </div>
    </section>
@endsection
