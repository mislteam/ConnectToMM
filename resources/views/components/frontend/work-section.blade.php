@props(['section'])
<section class="work-section position-relative">
    <div class="container">
        <figure class="element1 mb-0">
            <img src="./assets/images/what-we-do-icon-1.png" class="img-fluid" alt="">
        </figure>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="work-content" data-aos="fade-right">
                    <h6>{{ $section->eyebrow_text }}</h6>
                    <h2>{{ $section->title }}</h2>
                    <p class="text-size-18">{{ $section->description }}</p>
                    @php
                        $texts_list = $section->items->where('item_type', 'texts_list');
                        $btn_img_list = $section->items->where('item_type', 'btns_list');
                    @endphp
                    <div class="content">
                        <ul class="list-unstyled mb-0">
                            @foreach ($texts_list as $text)
                                <li class="h4">{{ $text->title }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="image_wrapper">
                        @foreach ($btn_img_list as $item)
                            <a class="item-image pl-2" href="{{ $item->button_url }}">
                                <figure class="mb-0 image-google">
                                    <img class="img-fluid"
                                        src="{{ $item->item_image ? asset('item/' . $item->item_image) : asset('assets/images/connection-card.png') }}"
                                        alt="">
                                </figure>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="work-wrapper">
                    <figure class="mobile-image mb-0">
                        <img src="{{ $section->image ? asset('section/' . $section->image) : asset('assets/images/default-mobile.png') }}"
                            alt="mobile image" class="img-fluid">
                    </figure>
                    <figure class="mobile-bg mb-0">
                        <img src="./assets/images/mobile-bg.png" alt="">
                    </figure>
                    <figure class="work-element1 mb-0">
                        <img src="./assets/images/work-element.png" alt="" class="img-fluid">
                    </figure>
                    <figure class="work-element mb-0">
                        <img src="./assets/images/work-element.png" alt="" class="img-fluid">
                    </figure>
                </div>
            </div>
        </div>
        <figure class="element2 mb-0">
            <img src="./assets/images/what-we-do-icon-2.png" class="img-fluid" alt="">
        </figure>
    </div>
</section>
