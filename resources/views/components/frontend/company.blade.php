@props(['section'])
<section class="what-we-do about-section position-relative">
    <div class="container">
        <figure class="element1 mb-0">
            <img src="./assets/images/what-we-do-icon-1.png" class="img-fluid" alt="">
        </figure>
        <div class="row">
            <div class="col-12">
                <div class="subheading" data-aos="fade-right">
                    <h6>{{ $section->eyebrow_text }}</h6>
                    <h2>{{ $section->title }}</h2>
                </div>
            </div>
        </div>
        @php
            $down_arrow = 'assets/images/what-we-do-arrow-1.png';
            $up_arrow = 'assets/images/what-we-do-arrow-2.png';
            $totalItems = isset($section->items) ? $section->items->count() : 0;
            $mixed_cards = $section->items->where('item_type', 'mixed_card');
        @endphp
        <div class="row position-relative">
            @foreach ($mixed_cards as $index => $item)
                <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                    <div class="service1 {{ $index == 1 ? 'service2' : '' }}">
                        <figure class="img img{{ $index + 1 }}">
                            <img src="{{ $item->item_image ? asset('item/' . $item->item_image) : asset('assets/images/mini-default.png') }}"
                                alt="image" class="img-fluid">
                        </figure>
                        <h3>{{ $item->title }}</h3>
                        <p class="text-size-18">{{ $item->description }}</p>
                        <a href="{{ $item->button_url }}" class="btn">{{ $item->button_text }}</a>
                    </div>
                </div>
                @if ($index < $totalItems - 1)
                    @if (($index + 1) % 2 == 1)
                        <figure class="arrow1 mb-0" data-aos="fade-down">
                            <img src="{{ asset($down_arrow) }}" class="img-fluid" alt="">
                        </figure>
                    @else
                        <figure class="arrow2 mb-0" data-aos="fade-up">
                            <img src="{{ asset($up_arrow) }}" class="img-fluid" alt="">
                        </figure>
                    @endif
                @endif
            @endforeach
        </div>
        <figure class="element2 mb-0">
            <img src="./assets/images/what-we-do-icon-2.png" class="img-fluid" alt="">
        </figure>
    </div>
</section>
