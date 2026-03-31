<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>Home | Connect To Myanmar</title>
    <!-- /SEO Ultimate -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta charset="utf-8">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/images/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/images/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/images/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/favicon/apple-icon-76x76.pn') }}g">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/images/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/images/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/images/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/images/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ asset('assets/images/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">
    <!-- Latest compiled and minified CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}"> --}}

    <link href="{{ asset('assets/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- StyleSheet link CSS -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/css/custom-style.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/css/special-classes.css') }}" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('assets/css/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2/select2-bootstrap4.min.css') }}">
</head>

<body>

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
    <script src="{{ asset('assets/js/custom.js') }}"></script>
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

        });
    </script>
</body>

</html>
