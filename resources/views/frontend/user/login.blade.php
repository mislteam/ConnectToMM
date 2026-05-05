@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <style>
        .login-form .password-wrap {
            position: relative;
        }

        .login-form .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #7a7a7a;
            font-size: 14px;
            font-weight: 600;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .login-form .password-toggle:focus {
            outline: none;
        }

        .login-form .social-login-label {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            color: #1f2430;
            font-weight: 600;
            line-height: 1;
        }

        .login-form .social-login-label::before,
        .login-form .social-login-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e6e6e6;
        }

        .login-form .social-auth-row {
            display: flex;
            gap: 12px;
            margin-top: 4px;
        }

        .login-form .social-auth-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            border: 1px solid #d8d8d8;
            border-radius: 12px;
            background: #fff;
            color: #1f2430;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .login-form .social-auth-btn:hover {
            border-color: #c6c6c6;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .login-form .social-auth-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            flex: 0 0 28px;
        }

        .login-form .social-auth-icon.google {
            color: #1877f2;
            border: 1px solid #d7dff5;
            background: #f7faff;
            font-family: Arial, sans-serif;
        }

        .login-form .social-auth-icon.facebook {
            color: #1877f2;
            border: 1px solid #d6e6ff;
            background: #f4f8ff;
            font-family: Arial, sans-serif;
        }

        .login-form .social-auth-btn.disabled {
            opacity: 0.72;
            pointer-events: none;
            cursor: not-allowed;
        }

        .login-form .join-now-outer a {
            color: var(--e-global-color-accent);
            font-weight: 600;
            text-decoration: none;
        }

        .login-form .join-now-outer a:hover {
            color: var(--e-global-color-secondary);
            text-decoration: underline;
        }

        @media (max-width: 575px) {
            .login-form .social-auth-row {
                flex-direction: column;
            }
        }
    </style>
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
                <p class="text-muted mb-0">Please Sign in.....</p>
            </div>
            <div class="login-form-box">
                <div class="login-card">
                    <form method="POST" action="{{ route('customer.login.submit') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Enter your e-mail</label>
                            <input class="input-field form-control" type="email" id="email" name="email"
                                value="{{ old('email') }}" placeholder="e.g. elon@tesla.com" autocomplete="email">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Enter your password</label>
                            <div class="password-wrap">
                                <input class="input-field form-control pr-5" type="password" id="password" name="password"
                                    placeholder="Password" autocomplete="current-password">
                                <button type="button" class="password-toggle" data-target="#password"
                                    data-label-show="Show" data-label-hide="Hide" aria-label="Show password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('customer.password.request') }}" class="forgot-password">Forgot
                                Password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                        <div class="text-center my-3 social-login-label">Or continue with</div>
                        <div class="social-auth-row">
                            <a href="{{ route('customer.google.redirect', ['flow' => 'login']) }}" class="social-auth-btn">
                                <span class="social-auth-icon google">
                                    <i class="fa-brands fa-google"></i>
                                </span>
                                <span>Google</span>
                            </a>

                            <a href="#" class="social-auth-btn">
                                <span class="social-auth-icon facebook">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </span>
                                <span>Facebook</span>
                            </a>
                        </div>
                    </form>
                </div>
                <div class="join-now-outer text-center">
                    <a class="mb-0" href="{{ route('user.register') }}">Join now, create your account</a>
                </div>
            </div>
        </div>
        <figure class="mb-0 need-layer">
            <img src="./assets/images/need-layer.png" alt="" class="img-fluid">
        </figure>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.password-toggle').forEach(function(button) {
                var targetSelector = button.getAttribute('data-target');
                var input = document.querySelector(targetSelector);

                if (!input) {
                    return;
                }

                button.addEventListener('click', function() {
                    var isPassword = input.getAttribute('type') === 'password';

                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    button.innerHTML = isPassword ?
                        '<i class="fa-regular fa-eye-slash"></i>' :
                        '<i class="fa-regular fa-eye"></i>';
                    button.setAttribute('aria-label', isPassword ? 'Hide password' :
                        'Show password');
                });
            });
        });
    </script>
@endsection
