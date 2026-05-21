@extends('frontend.layouts.index')
@section('title', 'Roam E-SIM Package')
@section('content')
    <div class="sub-banner">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="{{ asset('assets/images/homebanner-bgshape.png') }}" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>E-SIM - {{ $settings['roam_title']->value ?? 'Roam' }}</h1>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                                labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Our Service</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16">E-SIM</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">{{ $settings['roam_title']->value ?? 'Roam' }}</span>
        </div>
    </div>

    <section class="service-section esim-package-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content text-center esim-package-heading" data-aos="fade-right">
                        <h6>E-SIM - {{ $settings['roam_title']->value ?? 'Roam' }}</h6>
                        <h2>Package Plan</h2>
                    </div>
                </div>
            </div>

            <figure class="element1 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-1.png') }}" class="img-fluid" alt="">
            </figure>

            <div class="services-data mt-4">
                <div class="esim-package-stage">
                    <div class="esim-package-stage__title">
                        <h3>Search Results</h3>
                    </div>

                    <div class="esim-package-stage__body">
                        <div class="esim-package-grid" id="package-list-esim" data-package-grid="esim">
                            @forelse ($packageCards as $card)
                                @php
                                    $package = $card['package'];
                                    $roam = $card['roam'];
                                    $lowestPrice = $card['lowest_price'];
                                @endphp
                                <div class="package-card" data-package-card="esim">
                                    <div class="service-box esim-service-box">
                                        <figure class="img img2 mb-3">
                                            <img src="{{ file_exists(public_path('storage/upload/roam/' . $roam->image)) ? asset('storage/upload/roam/' . $roam->image) : asset($roam->image ?? 'assets/images/package.jpg') }}"
                                                alt="{{ $package->country_name }}" class="img-fluid">
                                        </figure>
                                        <div class="content">
                                            <h4>{{ $package->country_name }}</h4>
                                            <p class="text-size-16">From {{ number_format($lowestPrice) }} MMK</p>
                                            <a href="{{ route('esim.roampackageview', ['id' => $package->sku_id, 'list_view' => 1, 'sim_type' => session('sim_type', 'new_esim')]) }}"
                                                data-base-href="{{ route('esim.roampackageview', ['id' => $package->sku_id, 'list_view' => 1]) }}"
                                                class="more">View Offer</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="esim-empty-state">
                                        <strong>No packages available yet.</strong>
                                        <p>The selected SIM type does not have any package cards ready to show.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        @if ($packageCards->count() > 6)
                            <div class="text-center mt-4">
                                <button id="showMoreBtn-esim" class="btn btn-primary px-4 py-2"
                                    data-show-more="esim">Show All</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <figure class="element2 mb-0">
                <img src="{{ asset('assets/images/what-we-do-icon-2.png') }}" class="img-fluid" alt="">
            </figure>
        </div>
    </section>

    <style>
        .esim-package-section {
            position: relative;
            overflow: hidden;
        }

        .esim-package-heading h2 {
            font-size: 42px;
            line-height: 1.15;
            margin-bottom: 0;
        }

        .esim-package-stage {
            margin-top: 8px;
        }

        .esim-package-stage__title {
            margin-bottom: 18px;
            text-align: center;
        }

        .esim-package-stage__title h3 {
            font-size: 24px;
            font-weight: 700;
            color: #10243e;
            margin-bottom: 0;
        }

        .esim-package-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
            column-gap: 1rem;
            row-gap: 1.1rem;
            margin-top: 1rem;
        }

        .esim-package-grid .package-card {
            width: 100%;
            min-width: 0;
        }

        .esim-service-box {
            height: 100%;
        }

        .esim-service-box .img {
            margin-bottom: 0.65rem !important;
        }

        .esim-service-box .content {
            padding-bottom: 0.35rem;
        }

        .esim-service-box .content h4 {
            margin-bottom: 0.2rem;
        }

        .esim-service-box .content .text-size-16 {
            margin-bottom: 0.55rem;
        }

        .esim-service-box .content .more {
            margin-top: 0.15rem;
        }

        .esim-empty-state {
            padding: 1rem 1.15rem;
            border-radius: 18px;
            background: rgba(247, 249, 253, 0.9);
            border: 1px dashed rgba(18, 58, 134, 0.18);
            color: #3c4a63;
        }

        .esim-empty-state strong {
            display: block;
            margin-bottom: 0.35rem;
        }

        @media (max-width: 1199px) {
            .esim-package-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .esim-package-heading h2 {
                font-size: 30px;
            }

            .esim-package-grid {
                display: block;
            }

            .esim-service-box .img {
                margin-bottom: 0.5rem !important;
            }

            .esim-service-box .content .text-size-16 {
                margin-bottom: 0.45rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-package-grid]').forEach((grid) => {
                const items = grid.querySelectorAll('.package-card');
                const gridKey = grid.getAttribute('data-package-grid');
                const showMoreBtn = document.querySelector(`[data-show-more="${gridKey}"]`);
                let visibleCount = 6;

                if (!showMoreBtn || items.length === 0) {
                    if (showMoreBtn) {
                        showMoreBtn.style.display = 'none';
                    }
                    return;
                }

                items.forEach((item, index) => {
                    if (index >= visibleCount) {
                        item.style.display = 'none';
                    }
                });

                if (items.length <= visibleCount) {
                    showMoreBtn.style.display = 'none';
                    return;
                }

                showMoreBtn.addEventListener('click', function() {
                    let revealed = 0;
                    items.forEach((item, index) => {
                        if (index >= visibleCount && revealed < 6) {
                            item.style.display = 'block';
                            revealed++;
                        }
                    });

                    visibleCount += revealed;

                    if (visibleCount >= items.length) {
                        showMoreBtn.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection
