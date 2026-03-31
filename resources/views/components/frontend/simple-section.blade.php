@props(['section'])
<section class="bannermain position-relative">
    <figure class="mb-0 bgshape">
        <img src="./assets/images/homebanner-bgshape.png" alt="" class="img-fluid">
    </figure>
    <div class="container">
        <div class="row">
            <div class="col-lg-5 col-md-5 col-sm-12 col-12">
                <div class="banner" data-aos="fade-right">
                    @php
                        $formatted_text = implode('. ', explode(' ', $section->eyebrow_text));
                        $button_item = $section->items->where('item_type', 'button')->first();
                    @endphp
                    <h6>{{ $formatted_text }}</h6>
                    <h1>{{ $section->title }}</h1>
                    <p class="banner-text">{{ $section->description }}</p>
                    <div class="button"><a class="button_text"
                            href="{{ $button_item->button_url }}">{{ $button_item->button_text }}</a></div>
                </div>
            </div>
            <div class=" col-lg-7 col-md-7 col-sm-12">
                <div class="banner-wrapper">
                    <figure class="mb-0 homeelement1">
                        <img src="./assets/images/homeelement1.png" class="img-fluid" alt="">
                    </figure>
                    <figure class="mb-0 banner-image">
                        @if ($section->image)
                            <img src="{{ asset('section/' . $section->image) }}" class="img-fluid" alt="banner-image">
                        @else
                            <img src="{{ asset('assets/images/homebanner-image.png') }}" class="img-fluid"
                                alt="banner-image">
                        @endif
                    </figure>
                    <figure class="mb-0 content img-bg">
                        <img src="./assets/images/homebanner-img-bg.png" alt="banner-image-bg">
                    </figure>
                    <figure class="mb-0 homeelement">
                        <img src="./assets/images/homeelement.png" class="img-fluid" alt="">
                    </figure>
                </div>
            </div>
        </div>
    </div>
</section>
