<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>Home | Connect To Myanmar</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- /SEO Ultimate -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta charset="utf-8">
    <style>
        body:not(.app-ready),
        body.request-loader-active {
            overflow: hidden;
        }

        .request-loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(8, 15, 31, 0.62);
            backdrop-filter: blur(4px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        body:not(.app-ready) .request-loader-overlay,
        body.request-loader-active .request-loader-overlay {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .request-loader-panel {
            max-width: 320px;
            width: 100%;
            text-align: center;
            padding: 28px 24px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.18);
        }

        .request-loader-spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 16px;
            border-radius: 50%;
            border: 6px solid rgba(0, 123, 255, 0.15);
            border-top-color: #0d6efd;
            animation: request-loader-spin 0.9s linear infinite;
        }

        .request-loader-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .request-loader-text {
            margin: 0;
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.5;
        }

        @keyframes request-loader-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <link rel="apple-touch-icon" sizes="57x57"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-76x76.pn') }}g">
    <link rel="apple-touch-icon" sizes="114x114"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">
    <!-- Latest compiled and minified CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}"> --}}

    <link rel="stylesheet"
        href="{{ asset('assets/bootstrap/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/bootstrap/bootstrap.min.css')) }}">

    {{-- <link href="{{ asset('assets/bootstrap/bootstrap.min.css') }}" rel="stylesheet"> --}}

    <!-- Font Awesome link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- StyleSheet link CSS -->
    <link href="{{ asset('assets/css/style.css') }}?v{{ filemtime(public_path('assets/css/style.css')) }}"
        rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet"
        href="{{ asset('assets/css/custom-style.css') }}?v={{ filemtime(public_path('assets/css/custom-style.css')) }}"
        type="text/css">
    <link rel="stylesheet"
        href="{{ asset('assets/css/special-classes.css') }}?v={{ filemtime(public_path('assets/css/special-classes.css')) }}"
        type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('assets/css/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2/select2-bootstrap4.min.css') }}">
    @yield('styles')
</head>

<body>
    <div class="request-loader-overlay" data-request-loader-overlay aria-hidden="true">
        <div class="request-loader-panel" role="status" aria-live="polite">
            <div class="request-loader-spinner" aria-hidden="true"></div>
            <h2 class="request-loader-title">Loading</h2>
        </div>
    </div>

    @include('frontend.layouts.header')
    @yield('content')
    @include('frontend.layouts.footer')


    <!-- Latest compiled JavaScript -->
    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/app.js') }}"></script> --}}
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/video_link.js') }}"></script>
    <script src="{{ asset('assets/js/video.js') }}"></script>
    <script src="{{ asset('assets/js/counter.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}?v={{ filemtime(public_path('assets/js/custom.js')) }}"></script>
    <script src="{{ asset('assets/js/animation_links.js') }}"></script>
    <script src="{{ asset('assets/js/animation.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script type="text/javascript">
        $(".select2_design").select2({
            theme: 'bootstrap4',
            placeholder: "Search for destination...",
            allowClear: true
        });
    </script>

    <script>
        $(document).ready(function() {

            let hoverTimer;

            // Desktop only
            if (window.innerWidth >= 992) {
                $('.megamenu, .megamenu .dropdown-menu').hover(
                    function() {
                        clearTimeout(hoverTimer);
                        $('.megamenu').addClass('show')
                            .find('.dropdown-menu').addClass('show');
                    },
                    function() {
                        hoverTimer = setTimeout(function() {
                            $('.megamenu').removeClass('show')
                                .find('.dropdown-menu').removeClass('show');
                        }, 300);
                    }
                );
            }

        });
    </script>
    @yield('scripts')

</body>

</html>
