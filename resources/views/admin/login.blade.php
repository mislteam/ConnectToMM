<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Login | Connect To Myanmar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MOHT QR Code System">
    <meta name="keywords" content="MOHT QR Code System">
    <meta name="author" content="WebAppLayers">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Vendor css -->
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css">
</head>

<body>
    @include('components.alert')
    <div class="auth-box align-items-center d-flex h-100 position-relative card-side-img rounded-0 overflow-hidden">
        <div class="card-img-overlay auth-overlay d-flex align-items-center justify-content-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xxl-4 col-md-6 col-sm-8">
                        <div class="card p-4 rounded-2">
                            <div class="auth-brand text-center mb-4">
                                <a href="#" class="logo-dark">
                                    <img src="{{ asset('general/logo/' . $logo->value) }}" alt="Connect To MM"
                                        height="55px">
                                </a>
                                <h4 class="fw-bold mt-3">Admin Login</h4>
                            </div>
                            <form action="{{ route('login') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="email" name="email" class="form-control p-2 rounded-0"
                                            id="userEmail" placeholder="Email Address" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control p-2 rounded-0"
                                            id="userPassword" placeholder="Password" required>
                                    </div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary fw-semibold py-2 rounded-0">Log
                                        In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end auth-fluid-->

    <!-- Theme Config Js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

</body>

</html>
