@props(['section'])
<section class="about-repay">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="about-wrapper">
                    <figure class="circle mb-0">
                        <img src="./assets/images/image-2-bg.png" alt="">
                    </figure>
                    <div class="position-relative">
                        <a class="popup-vimeo" href="{{ $section->video }}">
                            <figure class="mb-0 videobutton">
                                <img class="thumb img-fluid" style="cursor: pointer"
                                    src="./assets/images/play-button.png" alt="">
                            </figure>
                        </a>
                    </div>
                    <figure class="image mb-0">
                        <img src="{{ $section->image ? asset('section/' . $section->image) : asset('assets/images/image-2.png') }}"
                            alt="" class="img-fluid">
                    </figure>
                    <figure class="homeelement mb-0">
                        <img src="./assets/images/homeelement.png" alt="" class="img-fluid">
                    </figure>
                    <figure class="homeelement1 mb-0">
                        <img src="./assets/images/homeelement.png" alt="" class="img-fluid">
                    </figure>
                </div>
            </div>
            @php
                $values = $section->items->values();
            @endphp
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="about-content" data-aos="fade-up">
                    <h6>{{ $section->eyebrow_text }}</h6>
                    <h2>{{ $section->title }}</h2>
                    <p class="text-size-18">{{ $section->description }}</p>
                    <div class="right-lower">
                        @isset($values[0])
                            <figure class="mb-0 icon">
                                <img src="{{ $values[0]->item_image ? asset('item/' . $values[0]->item_image) : asset('assets/images/mini-default.png') }}"
                                    alt="happy customer" class="img-fluid">
                            </figure>
                            <div class="content">
                                <span>{{ $values[0]->title }}</span>
                                <h4 class="mb-0">Happy Customers</h4>
                            </div>
                        @endisset
                        @isset($values[1])
                            <figure class="mb-0 icon">
                                <img src="{{ $values[0]->item_image ? asset('item/' . $values[1]->item_image) : asset('assets/images/mini-default.png') }}"
                                    alt="" class="img-fluid">
                            </figure>
                            <div class="content content1">
                                <span>{{ $values[1]->title }}</span>
                                <h4 class="mb-0">Total Transections</h4>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
