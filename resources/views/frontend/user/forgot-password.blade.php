@extends('frontend.layouts.index')
@section('title', 'Forgot Password')
@section('content')
    @include('components.alert')
    <section class="login-form d-flex align-items-center">
        <div class="container">
            <div class="login-form-title text-center">
                <a href="{{ route('Index') }}">
                    <figure class="login-page-logo">
                        <img src="assets/images/login-page-logo.png" alt="">
                    </figure>
                </a>
                <h2>Forgot your password?</h2>
                <p class="text-muted">Enter your email address and we will send you a reset code.</p>
            </div>

            <div class="login-form-box">
                <div class="login-card">
                    <form method="POST" action="{{ route('customer.password.email') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Your e-mail</label>
                            <input class="input-field form-control" type="email" id="email" name="email"
                                value="{{ old('email') }}" placeholder="e.g. elon@gmail.com" autocomplete="email">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Send reset code</button>
                    </form>
                </div>

                <div class="join-now-outer text-center">
                    <a class="mb-0" href="{{ route('user.login') }}">Back to login</a>
                </div>
            </div>
        </div>

        <figure class="mb-0 need-layer">
            <img src="./assets/images/need-layer.png" alt="" class="img-fluid">
        </figure>
    </section>
@endsection
