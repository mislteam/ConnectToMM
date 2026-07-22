@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    @php
        $file = get_banner('about_us');
        $image = $file !== null ? 'banner/' . $file : 'assets/images/default-banner.png';
    @endphp
    <div class="sub-banner" style="background-image: url({{ asset($image) }})">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="{{ asset('assets/images/homebanner-bgshape.png') }}" alt="" class="img-fluid">
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
    <!--About-->
    <x-frontend.company :section="$company" />
    <!--About Repay-->
    <x-frontend.about-repay :section="$about_repay" />
    <!--How we Work-->
    <x-frontend.work-section :section="$work_section" />
    <!--FAQ section-->
    <x-frontend.faq-section :section="$faq_section" :faqs="$faqs" />
    <!--Our Blog-->
    <section class="blog-section position-relative">
        <div class="container">
            <figure class="element1 mb-0">
                <img src="./assets/images/what-we-do-icon-1.png" class="img-fluid" alt="">
            </figure>
            <div class="row">
                <div class="col-12">
                    <div class="subheading">
                        <h6>OUR BLOG</h6>
                        <h2>Latest Blog & Articles</h2>
                    </div>
                </div>
            </div>
            <div class="row" data-aos="fade-up">
                @forelse ($blogs as $blog)
                    <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                        <div class="blog-box">
                            <figure>
                                <img src="{{ $blog->image ? asset('blog/' . $blog->image) : asset('assets/images/firstblog.png') }}"
                                    width="100%" height="210px" style="background-size: cover; object-fit: cover;">
                            </figure>
                            <div class="content content1">
                                <a class="button">{{ $blog->category->cat_name }}</a>
                                <p class="h4">{{ $blog->title }}</p>
                                <a href="{{ route('blogDetail', $blog->id) }}"
                                    class="text-size-16 text-decoration-none">Read
                                    More</a>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <figure class="element2 mb-0">
                <img src="./assets/images/what-we-do-icon-2.png" class="img-fluid" alt="">
            </figure>
        </div>
    </section>
    <!-- Partner -->


@endsection
