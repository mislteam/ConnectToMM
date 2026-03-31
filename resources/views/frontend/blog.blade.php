@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <!-- Sub-Banner -->
    @php
        $file = get_banner('blog');
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
    <!-- MAIN SECTION -->
    <!--End Slider Section-->
    <section class="blog-posts">
        <div class="container">
            <div class="row wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
                <div id="blog" class="three-column col-xl-12">
                    <div class="row">
                        @foreach ($blogs as $blog)
                            <div class="col-xl-4 col-lg-4">
                                <div class="float-left w-100 post-item border mb-4">
                                    <div class="post-item-wrap position-relative">
                                        <div class="post-image">
                                            <a href="#">
                                                <img alt=""
                                                    src="{{ $blog->image ? asset('blog/' . $blog->image) : asset('assets/images/standard_post_img01.jpg') }}">
                                            </a>
                                            <span class="post-meta-category">
                                                <a href="">{{ $blog->category->cat_name }}</a>
                                            </span>
                                            <!--post-image-->
                                        </div>
                                        <div class="post-item-description">
                                            <h2>
                                                <a href="#">{{ $blog->title }}</a>
                                            </h2>
                                            <p class="text-size-16">{{ Str::Limit($blog->desc, 150) }}</p>
                                            <a href="#" class="item-link">Read More <i
                                                    class="fa fa-arrow-right"></i></a>
                                            <!--post-item-description-->
                                        </div>
                                        <!--post-item-wrap-->
                                    </div>
                                    <!--post-item-->
                                </div>
                                <!--col-->
                            </div>
                        @endforeach
                    </div>
                    <!--blog-->
                </div>
                <!--row-->
            </div>
            <!--container-->
        </div>
    </section>
    <!-- Partner -->

@endsection
