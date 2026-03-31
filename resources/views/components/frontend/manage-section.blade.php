@props(['section'])
<section class="manage-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="manage-content" data-aos="fade-right">
                    <h2>{{ $section->title }}</h2>
                    @php
                        $manage_cards = $section->items->where('item_type', 'manage_card')->values();
                    @endphp
                    @foreach ($manage_cards as $card)
                        <div class="first">
                            <div class="row">
                                <div class="col-lg-2 col-md-2 col-sm-12 col-12">
                                    <figure class="mb-0 icon">
                                        <img src="{{ $card->item_image ? asset('item/' . $card->item_image) : asset('assets/images/mini-default.png') }}"
                                            alt="manage card">
                                    </figure>
                                </div>
                                <div class="col-lg-10 col-md-10 col-sm-12 col-12">
                                    <div class="content">
                                        <h4>{{ $card->title ?? '' }}</h4>
                                        <p class="text-size-16 text">{{ $card->description ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="manage-wrapper">
                    <figure class="mb-0 homeelement1">
                        <img src="./assets/images/homeelement1.png" class="img-fluid" alt="">
                    </figure>
                    <figure class="mb-0 manage-image">
                        <img src="{{ $section->image ? asset('section/' . $section->image) : asset('assets/images/manage-your-everything-image.png') }}"
                            class="img-fluid" alt="">
                    </figure>
                    <figure class="mb-0 content img-bg">
                        <img src="./assets/images/manageyour-mange-your-bg.png" alt="" class="">
                    </figure>
                    <figure class="mb-0 homeelement">
                        <img src="./assets/images/homeelement.png" class="img-fluid" alt="">
                    </figure>
                </div>
            </div>
        </div>
    </div>
    <figure class="mb-0 manage-layer">
        <img src="./assets/images/mange-layer.png" alt="" class="img-fluid">
    </figure>
</section>
