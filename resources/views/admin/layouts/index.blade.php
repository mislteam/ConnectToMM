<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <title>{{ $settings['title'] ? $settings['title']->value : '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MOHT QR Code System">
    <meta name="keywords" content="MOHT QR Code System">
    <meta name="author" content="MISL">
    <script>
        (function() {
            try {
                var stored = localStorage.getItem("c2mm-theme");
                var prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
                var theme = stored || (prefersDark ? "dark" : "light");
                document.documentElement.setAttribute("data-bs-theme", theme);
            } catch (e) {
                document.documentElement.setAttribute("data-bs-theme", "light");
            }
        })();
    </script>
    <!-- App favicon -->
    <link rel="shortcut icon"
        href="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : asset('assets/images/favicon/android-icon-96x96.png') }}">

    <!-- Vendor css -->
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Vector Maps css -->
    <link href="{{ asset('assets/plugins/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Font Awesome 5 Free -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Filepond Plugin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/filepond/filepond.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/plugins/filepond/filepond-plugin-image-preview.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/dropzone/dropzone.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/quill/quill.core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/quill/quill.snow.css') }}">
    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('assets/css/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2/select2-bootstrap4.min.css') }}">

    <!-- spinner -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/spinkit/spinkit.min.css') }}">

    <link href="{{ asset('assets/plugins/summernote/summernote-bs5.min.css') }}" rel="stylesheet">

    <style>
        body.app-loading,
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

        body.app-loading .request-loader-overlay,
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

        [data-bs-theme="dark"] .text-black {
            color: #e5edf9 !important;
        }

        @keyframes request-loader-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

</head>

<body class="app-loading request-loader-active">
    <div class="request-loader-overlay" data-request-loader-overlay aria-hidden="true">
        <div class="request-loader-panel" role="status" aria-live="polite">
            <div class="request-loader-spinner" aria-hidden="true"></div>
            <h2 class="request-loader-title">Loading</h2>
        </div>
    </div>
    <div class="wrapper">
        <!-- Sidenav Menu Start -->
        @include('admin.layouts.sidebar')
        <!-- Sidenav Menu End -->


        <!-- Topbar Start -->
        @include('admin.layouts.header')
        <!-- Topbar End -->

        <div class="content-page">
            @yield('content')

            <!-- Footer Start -->
            @include('admin.layouts.footer')
            <!-- Footer End -->
        </div>



    </div>
    <!-- END wrapper -->
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>


    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>

    <!-- Theme Config Js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
    <script>
        (function() {
            var STORAGE_KEY = "c2mm-theme";

            document.addEventListener("DOMContentLoaded", function() {
                var toggle = document.getElementById("light-dark-mode");
                if (!toggle) {
                    return;
                }

                function syncThemeState() {
                    var theme = document.documentElement.getAttribute("data-bs-theme") === "dark" ? "dark" :
                        "light";

                    toggle.setAttribute(
                        "aria-label",
                        theme === "dark" ? "Switch to light mode" : "Switch to dark mode",
                    );

                    try {
                        localStorage.setItem(STORAGE_KEY, theme);
                    } catch (e) {}
                }

                syncThemeState();

                var observer = new MutationObserver(function(mutations) {
                    for (var i = 0; i < mutations.length; i++) {
                        if (mutations[i].attributeName === "data-bs-theme") {
                            syncThemeState();
                            break;
                        }
                    }
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ["data-bs-theme"]
                });
            });
        })();
    </script>

    <!-- Plugins js -->
    {{-- <script src="{{ asset('assets/plugins/apexcharts/apexcharts.min.js') }}"></script> --}}

    <!-- Custom table -->
    <script src="{{ asset('assets/js/pages/custom-table.js') }}"></script>

    <!-- Dashboard 2 Page js -->
    <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
    <!-- Filepond Plugin Js -->
    <script src="{{ asset('assets/plugins/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/filepond/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/filepond/filepond-plugin-file-validate-size.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/filepond/filepond-plugin-file-validate-type.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/filepond/filepond-plugin-image-exif-orientation.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/filepond/filepond-plugin-file-encode.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-fileupload.js') }}"></script>

    <!-- Vector Map Demo js-->

    <script src="{{ asset('assets/plugins/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/plugins/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert/sweetalert2@11.js') }}"></script>
    <!-- Summernote Plugin Js -->
    <script src="{{ asset('assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
    <!-- Summernote Demo Js -->
    <script src="{{ asset('assets/js/pages/form-summernote.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/excel/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/backend-custom-js.js') }}"></script>

    <script type="text/javascript">
        $(".select2_design").select2({
            theme: 'bootstrap4',
            placeholder: $(this).data('placeholder'),
            allowClear: true
        });
    </script>
    <script>
        $(function() {
            FilePond.setOptions({
                storeAsFile: true
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.megamenu').hover(
                function() {
                    $(this).addClass('show');
                    $(this).find('.dropdown-menu').addClass('show');
                },
                function() {
                    $(this).removeClass('show');
                    $(this).find('.dropdown-menu').removeClass('show');
                }
            );
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dashboardUrl = "{{ route('dashboard.index') }}";
            const linkEls = document.querySelectorAll('.breadcrumb-item a');
            if (linkEls) {
                linkEls.forEach((el) => {
                    if (el.textContent.toLowerCase() === 'home') {
                        el.href = dashboardUrl ?? '#';
                    }
                })
            }
        })
    </script>
</body>

</html>
