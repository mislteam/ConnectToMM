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
                            <h1>Physical-SIM - Roam</h1>
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
                class="mb-0 text-size-16 box_span">Roam</span>
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
                    <li><a class="nav-link active" data-toggle="tab" href="#roam-new-physical"> New Physical-SIM</a></li>
                    <li><a class="nav-link" data-toggle="tab" href="#roam-recharge">Recharge</a></li>
                </ul>
                <div class="tab-content mt-4 bg-light">
                    <div role="tabpanel" id="roam-new-physical" class="tab-pane active">
                        <div class="panel-body">
                            <div class="message_content" data-aos="fade-up">
                                <form method="get" action="{{ route('physical.roamsearch') }}">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <h4>Shop for the best Physical SIM offers</h4>
                                                <select class="select2_design form-control" multiple="multiple" name="countryname[]">
                                                    @foreach ($countrys as $countryname)
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
                                </form>
                                <!-- <form method="POST">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <h4>Shop for the best eSIM offers:</h4>
                                                <select class="select2_design form-control" multiple="multiple">
                                                    <option value="Mayotte">Mayotte</option>
                                                    <option value="Mexico">Mexico</option>
                                                    <option value="Micronesia, Federated States of">Micronesia, Federated
                                                        States of</option>
                                                    <option value="Moldova, Republic of">Moldova, Republic of</option>
                                                    <option value="Monaco">Monaco</option>
                                                    <option value="Mongolia">Mongolia</option>
                                                    <option value="Montenegro">Montenegro</option>
                                                    <option value="Montserrat">Montserrat</option>
                                                    <option value="Morocco">Morocco</option>
                                                    <option value="Mozambique">Mozambique</option>
                                                    <option value="Myanmar">Myanmar</option>
                                                    <option value="Namibia">Namibia</option>
                                                    <option value="Nauru">Nauru</option>
                                                    <option value="Nepal">Nepal</option>
                                                    <option value="Netherlands">Netherlands</option>
                                                    <option value="New Caledonia">New Caledonia</option>
                                                    <option value="New Zealand">New Zealand</option>
                                                    <option value="Nicaragua">Nicaragua</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mt-4 text-center">
                                                <a href="{{ route('physical.roamsearch') }}" class="button_text">Continue Search</a>
                                            </div>
                                        </div>
                                    </div>
                                </form> -->
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" id="roam-recharge" class="tab-pane">
                        <div class="panel-body">
                            <div class="message_content" data-aos="fade-up">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <h4>SIM ICCID Number</h4>
                                                <input type="text" class="form_style"
                                                    placeholder="Enter Your SIM ICCID No.">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <h4>Shop for the best Physical SIM offers:</h4>
                                                <select class="select2_design form-control" multiple="multiple">
                                                    <option value="Mayotte">Mayotte</option>
                                                    <option value="Mexico">Mexico</option>
                                                    <option value="Micronesia, Federated States of">Micronesia, Federated
                                                        States of</option>
                                                    <option value="Moldova, Republic of">Moldova, Republic of</option>
                                                    <option value="Monaco">Monaco</option>
                                                    <option value="Mongolia">Mongolia</option>
                                                    <option value="Montenegro">Montenegro</option>
                                                    <option value="Montserrat">Montserrat</option>
                                                    <option value="Morocco">Morocco</option>
                                                    <option value="Mozambique">Mozambique</option>
                                                    <option value="Myanmar">Myanmar</option>
                                                    <option value="Namibia">Namibia</option>
                                                    <option value="Nauru">Nauru</option>
                                                    <option value="Nepal">Nepal</option>
                                                    <option value="Netherlands">Netherlands</option>
                                                    <option value="New Caledonia">New Caledonia</option>
                                                    <option value="New Zealand">New Zealand</option>
                                                    <option value="Nicaragua">Nicaragua</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mt-4 text-center">
                                                <a href="{{ route('physical.roamsearch') }}" class="button_text">Continue Search</a>
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
                        <h6>Physical-SIM - Roam</h6>
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
                    <!-- <ul class="nav" role="tablist">
                        <li><a class="nav-link active" data-toggle="tab" href="#roam-new-esim-package"> New eSIM</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#roam-recharge-package">Recharge</a></li>
                    </ul> -->
                    <div class="tab-content mt-4 shadow-none p-0">
                        <div role="tabpanel" id="roam-new-physical-package" class="tab-pane active">
                            <div class="panel-body">
                                <div class="row" id="package-list">
                                        @foreach($skupackages as $package)
                                        @php
                                            $pkg = App\Models\RoamPhysical::where('sku_id', $package->sku_id)->first();

                                            // safest extraction
                                            $firstPackage = $pkg->packages[0] ?? null;

                                            $price = $firstPackage['price'] ?? 0;
                                            $priceId = $firstPackage['priceid'] ?? null;

                                            $matchedPrice = $priceId
                                                ? $priceList->firstWhere('product_code', $priceId)
                                                : null;
                                        @endphp
                                        @if(!empty($firstPackage) && $matchedPrice)
                                        <div class="col-lg-4  col-md-4 col-sm-12 col-12" >
                                            <div class="service-box">
                                                <figure class="img img2 mb-3">
                                                    <img src="{{  file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}" alt=""
                                                        class="img-fluid">
                                                </figure>
                                                <div class="content">
                                                    <h4>{{ $package->country_name }} </h4>
                                                    @if ($matchedPrice)
                                                        <p class="text-size-16">From {{ number_format($matchedPrice->price) }} MMK</p>
                                                    @else
                                                        <p class="text-size-16 text-danger">Not available</p>
                                                    @endif
                                                    <a href="{{ route('physical.roampackageview', ['id' => $package->sku_id]) }}" class="more">View Offer</a>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                </div>
                                 <div class="text-center mt-4">
                                    <button id="showMoreBtn" class="btn btn-primary px-4 py-2">Show More</button>
                                </div>
                            </div>
                        </div>
                       
                    </div>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const items = document.querySelectorAll('#package-list .col-lg-4');
    let visibleCount = 6; // show first 6 (2 rows)

    // Hide items after the 6th
    items.forEach((item, index) => {
        if (index >= visibleCount) {
            item.style.display = 'none';
        }
    });

    document.getElementById('showMoreBtn').addEventListener('click', function() {
        let revealed = 0;

        items.forEach((item, index) => {
            if (index >= visibleCount && revealed < 6) {
                item.style.display = 'block';
                revealed++;
            }
        });

        visibleCount += 6;


        // If no more items, hide button
        if (visibleCount >= items.length) {
            this.style.display = 'none';
        }
    });
});
</script>

@endsection
