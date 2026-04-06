@php
    $contactInfo = \App\Models\ContactInfo::first();
    $importantLinks = \App\Models\Link::where('type', 'important')->get();
    $supports = \App\Models\Link::where('type', 'support')->get();
    $logo = \App\Models\GeneralSetting::where('type', 'file')->first();
@endphp
<section class="footer-section">
    <div class="partner-section">
        <div class="container">
            <div class="partner">
                <ul class="mb-0 list-unstyled">
                    <li>
                        <figure class="mb-0 partner1">
                            <img class="img-fluid"
                                src="{{ $contactInfo->joytel_image ? asset('general/sim_imgs/' . $contactInfo->joytel_image) : asset('assets/images/footer-default-img.png') }}"
                                alt="joytel-img">
                        </figure>
                    </li>
                    <li>
                        <figure class="mb-0 partner1 partner2">
                            <img class="img-fluid"
                                src="{{ $contactInfo->roam_image ? asset('general/sim_imgs/' . $contactInfo->roam_image) : asset('assets/images/footer-default-img.png') }}"
                                alt="roam-img">
                        </figure>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="middle-portion">
            <div class="row">
                <div class="col-lg-4 col-md-5 col-sm-6 col-12">
                    <a href="./index.html">
                        <figure class="footer-logo">
                            <img src="{{ asset('general/logo/' . $logo->value) }}" class="img-fluid w-75"
                                alt="">
                        </figure>
                    </a>
                    <p class="text-size-16 footer-text">{{ $contactInfo->description }}</p>
                    <figure class="mb-0 payment-icon">
                        <img src="{{ asset('assets/images/payment-card.png') }}" class="img-fluid" alt="">
                    </figure>
                </div>
                <div class="col-lg-1 col-md-1 col-sm-12 col-12 d-lg-block d-none">

                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 col-12 d-md-block d-none">
                    <div class="links">
                        <h4 class="heading">Important Link</h4>
                        <hr class="line">
                        <ul class="list-unstyled mb-0">
                            @foreach ($importantLinks as $im)
                                <li>
                                    <a href="{{ $im->link }}"
                                        class=" text-size-16 text text-decoration-none">{{ $im->text }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-12 d-lg-block d-none">
                    <div class="links">
                        <h4 class="heading">Support</h4>
                        <hr class="line">
                        <ul class="list-unstyled mb-0">
                            @foreach ($supports as $support)
                                <li><a href="{{ $support->link }}"
                                        class=" text-size-16 text text-decoration-none">{{ $support->text }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 d-sm-block">
                    <div class="icon">
                        <h4 class="heading">Get in Touch</h4>
                        <hr class="line">
                        <ul class="list-unstyled mb-0">
                            <li class="text-size-16 text">Email: <a href="mailto:{{ $contactInfo->email }}"
                                    class="mb-0 text text-decoration-none text-size-16">{{ $contactInfo->email }}</a>
                            </li>
                            <li class="text-size-16 text">Phone:
                                <a href="#" class="mb-0 text text-decoration-none text-size-16">
                                    {{ $contactInfo->phone }}
                                </a>
                            </li>
                            <li class="social-icons">
                                @if ($contactInfo->social_media_links !== null)
                                    @foreach ($contactInfo->social_media_links as $media)
                                        <div class="circle"><a href="{{ $media['link'] }}"><i
                                                    class="{{ $media['icon'] }}"></i></a></div>
                                    @endforeach
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--footer area-->
<div class="copyright">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p class="text-size-16">Copyright @ {{ date('Y') }} Connect To Myanmar. All Rights Reserved</p>
            </div>
        </div>
    </div>
</div>
