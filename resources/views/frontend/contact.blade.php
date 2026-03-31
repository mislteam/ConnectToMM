@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    <!-- Sub-Banner -->
    @php
        $file = get_banner('contact_us');
        $image = $file !== null ? 'banner/' . $file : 'assets/images/default-banner.png';
    @endphp
    @include('components.alert')
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
    <!--Contact-->
    <section class="message-section">
        <div class="container">
            <figure class="element1 mb-0">
                <img src="./assets/images/what-we-do-icon-1.png" class="img-fluid" alt="">
            </figure>
            <div class="row position-relative">
                <div class="col-12">
                    <div class="content">
                        <h6>Let's Contact Us</h6>
                        <h2>Get in Touch with Us</h2>
                        <figure class="element3 mb-0">
                            <img src="./assets/images/what-we-do-element.png" alt="" class="img-fluid">
                        </figure>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                    <div class="message_content" data-aos="fade-up">
                        <form id="contactpage" method="POST" action="{{ route('contact.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <h4>Name:</h4>
                                        <input type="text" class="form_style" placeholder="Enter Your Name"
                                            name="name">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <h4>Email:</h4>
                                        <input type="email" class="form_style" placeholder="Enter Your Email Address"
                                            name="email">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <h4>Phone:</h4>
                                        <input type="tel" class="form_style" placeholder="Enter Your Phone Number"
                                            name="phone">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <h4>Message:</h4>
                                        <textarea class="form_style" placeholder="Add Your Comment" rows="3" name="msg"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="manage-button text-center">
                                <button type="submit" class="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                    <div class="map">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3152.3329737833114!2d144.96011341590386!3d-37.80566904135444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d4c2b349649%3A0xb6899234e561db11!2sEnvato!5e0!3m2!1sen!2s!4v1669200882885!5m2!1sen!2s"
                            style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <figure class="element2 mb-0">
                        <img src="./assets/images/what-we-do-icon-2.png" class="img-fluid" alt="">
                    </figure>
                </div>
            </div>
        </div>
    </section>
    <!-- need more help? -->
    <x-frontend.section-item :section="$section" />
    <!-- Partner -->
@endsection
