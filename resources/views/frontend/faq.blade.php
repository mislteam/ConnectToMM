@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    @php
        $file = get_banner('faq');
        $image = $file !== null ? 'banner/' . $file : 'assets/images/default-banner.png';
    @endphp
    <div class="sub-banner" style="background-image: url({{ asset($image) }})">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="./assets/images/homebanner-bgshape.png" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>{{ $banner->title ?? '' }}</h1>
                            <p>{{ $banner->subtitle ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Home</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">{{ $banner->page ?? '' }}</span>
        </div>
    </div>
    <!--FAQ section-->
    <section class="faq-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="subheading">
                        <h6>General Questions</h6>
                        <h2>Frequently Asked Questions</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                @php
                    $chunks = $faqs->chunk(ceil($faqs->count() / 2));
                @endphp

                @foreach ($chunks as $chunk)
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <div class="faq" data-aos="fade-up">
                            <div class="accordian-section-inner position-relative">
                                <div class="accordian-inner">
                                    <div id="accordion{{ $loop->index }}">
                                        @foreach ($chunk as $faq)
                                            <div class="accordion-card">
                                                <div class="card-header"
                                                    id="heading{{ $loop->parent->index }}{{ $loop->index }}">
                                                    <a href="#" class="btn btn-link collapsed" data-toggle="collapse"
                                                        data-target="#collapse{{ $loop->parent->index }}{{ $loop->index }}"
                                                        aria-expanded="false"
                                                        aria-controls="collapse{{ $loop->parent->index }}{{ $loop->index }}">
                                                        <h4>{{ $faq->title }}</h4>
                                                    </a>
                                                </div>
                                                <div id="collapse{{ $loop->parent->index }}{{ $loop->index }}"
                                                    class="collapse"
                                                    aria-labelledby="heading{{ $loop->parent->index }}{{ $loop->index }}">
                                                    <div class="card-body">
                                                        <p class="text-size-16 text-left mb-0 p-0">
                                                            {{ $faq->description }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- need more help? -->
    <x-frontend.section-item :section="$section" />
    <!-- Partner -->
@endsection
