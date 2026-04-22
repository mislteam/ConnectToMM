@props(['section', 'faqs'])

<section class="accordian-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 col-md-6 col-sm-12 col-12">
                <div class="faq">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="accordian-section-inner position-relative" data-aos="fade-up">
                                <div class="accordian-inner">
                                    <div id="accordion1">
                                        @foreach ($faqs as $faq)
                                            @php
                                                $index = $loop->index;
                                            @endphp
                                            <div class="accordion-card">
                                                <div class="card-header" id="heading{{ $index }}">
                                                    <a href="#" class="btn btn-link collapsed"
                                                        data-toggle="collapse"
                                                        data-target="#collapse{{ $index }}" aria-expanded="false"
                                                        aria-controls="collapse{{ $index }}">
                                                        <h4>{{ $faq->title }}</h4>
                                                    </a>
                                                </div>
                                                <div id="collapse{{ $index }}" class="collapse"
                                                    aria-labelledby="heading{{ $index }}" data-parent>
                                                    <div class="card-body">
                                                        <p class="text-size-16 text-left mb-0 p-0">
                                                            {{ $faq->description }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-1 col-md-1 col-sm-1 d-lg-block d-none">
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="accordion-content">
                    <h6>{{ $section->eyebrow_text ?? '' }}</h6>
                    <h2>{{ $section->title }}</h2>
                    <p class="text-size-18">{{ $section->description }}</p>
                    @php
                        $connection_methods = $section->items->where('item_type', 'connection_card')->values();
                    @endphp
                    <div class="right-lower" data-aos="fade-right">
                        @foreach ($connection_methods as $index => $item)
                            <figure class="mb-0 icon">
                                <img src="{{ $item->item_image ? asset('item/' . $item->item_image) : asset('assets/images/mini-default.png') }}"
                                    alt="" class="img-fluid">
                            </figure>
                            <div class="content {{ $index == 1 ? 'content1' : '' }}">
                                <span class="text-size-18">{{ $index == 0 ? 'Email Address' : 'Phone Number' }}</span>
                                <h4 class="mb-0">{{ $item->button_text ?? $item->button_url }}</h4>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
    <figure class="mb-0 manage-layer">
        <img src="./assets/images/mange-layer.png" alt="" class="img-fluid">
    </figure>
</section>
