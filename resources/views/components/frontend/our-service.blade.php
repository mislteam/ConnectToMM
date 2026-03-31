@props(['section'])
<section class="service-section">
    <div class="container">
        <div class="row position-relative">
            <div class="service-content">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <figure class="mb-0 services-icon">
                        <img src="./assets/images/services-our-services-icon-1.png" class="img-fluid" alt="">
                    </figure>
                    <h6>{{ $section->eyebrow_text }}</h6>
                    <h2>{{ $section->title }}</h2>
                    <figure class="service-image" data-aos="fade-up">
                        <img src="{{ $section->image ? asset('section/' . $section->image) : asset('assets/images/services-our-services-image.png') }}"
                            class="img-fluid" alt="">
                    </figure>
                </div>
            </div>
        </div>
        <figure class="element1 mb-0">
            <img src="./assets/images/what-we-do-icon-1.png" class="img-fluid" alt="">
        </figure>
        <div class="services-data">
            <div class="row align-items-center">
                @php
                    $simCards = $section->items->where('item_type', 'sim_card')->values();
                    $simImage = $section->items->firstWhere('item_type', 'sim_img');
                @endphp
                @if (isset($simCards[0]))
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="service-box">
                            <figure class="img img4">
                                <img src="{{ asset('assets/images/services-friendly.png') }}" class="img-fluid">
                            </figure>
                            <div class="content">
                                <h3>{{ $simCards[0]->title }}</h3>
                                <p>{{ $simCards[0]->description }}</p>
                                <a href="{{ $simCards[0]->button_url }}" class="more">
                                    {{ $simCards[0]->button_text }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-lg-4 col-md-4 col-sm-12 text-center">
                    <figure class="mb-0 mobile-image" data-aos="fade-right">
                        <img src="{{ $simImage && $simImage->item_image
                            ? asset('item/' . $simImage->item_image)
                            : asset('assets/images/services-mobile-image.png') }}"
                            class="img-fluid">
                    </figure>
                </div>

                @if (isset($simCards[1]))
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="service-box">
                            <figure class="img img4">
                                <img src="{{ asset('assets/images/services-friendly.png') }}" class="img-fluid">
                            </figure>
                            <div class="content">
                                <h3>{{ $simCards[1]->title }}</h3>
                                <p>{{ $simCards[1]->description }}</p>
                                <a href="{{ $simCards[1]->button_url }}" class="more">
                                    {{ $simCards[1]->button_text }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

        </div>
        <figure class="element2 mb-0">
            <img src="./assets/images/what-we-do-icon-2.png" class="img-fluid" alt="">
        </figure>
    </div>
</section>
