@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <!-- Sign-Up Form section-->
    <section class="login-form sign-up-form d-flex align-items-center">
        <div class="container">
            <div class="login-form-title text-center">
                <h2>Create your FREE account.</h2>
            </div>
            <div class="login-form-box">
                <div class="login-card">
                    <form>
                        <div class="form-group">
                            <label for="exampleInputName1">Your full name *</label>
                            <input class="input-field form-control" type="text" id="exampleInputName1"
                                placeholder="e.g. Elon Musk">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Your e-mail *</label>
                            <input class="input-field form-control" type="password" id="exampleInputEmail1"
                                placeholder="e.g. elon@gmail.com.com">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Enter your password *</label>
                            <input class="input-field form-control" type="password" id="exampleInputPassword1"
                                placeholder="Password">
                        </div>
                        <div>
                            <label class="font-weight-normal mt-md-3 mt-2 mb-md-4 mb-3" style="cursor: pointer;">
                                <input class="checkbox" type="checkbox">
                                I have agree terms and condition
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary mb-0">Register Now</button>
                    </form>
                </div>
                <div class="join-now-outer text-center">
                    <a class="mb-0" href="{{ route('user.login') }}">Already have an account?</a>
                </div>
            </div>
        </div>
        <figure class="mb-0 need-layer">
            <img src="./assets/images/need-layer.png" alt="" class="img-fluid">
        </figure>
    </section>
@endsection
