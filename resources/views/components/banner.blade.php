@props(['key'])
@php
    $banner = \App\Models\Banner::where('banner_type', $key)->first();
    $file = get_banner($key);
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
        <span class="mb-0 text-size-16">Home</span><span class="mb-0 text-size-16 dash">-</span>
        <span class="mb-0 text-size-16">Service</span><span class="mb-0 text-size-16 dash">-</span>
        <span class="mb-0 text-size-16 box_span">{{ $banner->page ?? '' }}</span>
    </div>
</div>
