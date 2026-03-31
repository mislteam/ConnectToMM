@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <!-- Login Form section-->
    <section class="login-form d-flex align-items-center">
        <div class="container">
            <div class="login-form-title text-center">
                <a href="index.html">
                    <figure class="login-page-logo">
                        <img src="assets/images/login-page-logo.png" alt="">
                    </figure>
                </a>
                <h2>Welcome back!</h2>
            </div>
            <div class="login-form-box">
                <div class="login-card">
                    <form>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Enter your e-mail</label>
                            <input class="input-field form-control" type="email" id="exampleInputEmail1"
                                placeholder="e.g. elon@tesla.com">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Enter your password</label>
                            <input class="input-field form-control" type="password" id="exampleInputPassword1"
                                placeholder="Password">
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                        <div>
                            <label class="mb-0" style="cursor: pointer;">
                                <input class="checkbox" type="checkbox" name="userRememberMe">
                                Remember me
                            </label>
                            <a href="#" class="forgot-password float-right">Lost Password?</a>
                        </div>
                    </form>
                </div>
                <div class="join-now-outer text-center">
                    <a class="mb-0" href="{{ route('user.register') }}">Join now, create your FREE account</a>
                </div>
            </div>
        </div>
        <figure class="mb-0 need-layer">
            <img src="./assets/images/need-layer.png" alt="" class="img-fluid">
        </figure>
    </section>
@endsection
