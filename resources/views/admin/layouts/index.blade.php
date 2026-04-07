<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard | Connect To Myanmar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MOHT QR Code System">
    <meta name="keywords" content="MOHT QR Code System">
    <meta name="author" content="MISL">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

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

</head>

<body>
    <!-- Begin page -->
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
        <!-- ============================================================== -->
        <!-- End of Main Content -->
        <!-- ============================================================== -->



    </div>
    <!-- END wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2/dist/fuse.min.js"></script>

    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>


    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>

    <!-- Theme Config Js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        $(".select2_design").select2({
            theme: 'bootstrap4',
            placeholder: "Choose Region",
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
</body>

</html>
