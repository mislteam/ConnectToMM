@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <style>
        .sign-up-form .login-card label,
        .sign-up-form .terms-label {
            color: #1f2430;
            font-weight: 600;
        }

        .sign-up-form .terms-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            line-height: 1.4;
        }

        .sign-up-form .terms-label input {
            flex: 0 0 auto;
            margin-top: 0;
        }

        .sign-up-form .password-wrap {
            position: relative;
        }

        .sign-up-form .password-toggle {
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

        .sign-up-form .password-toggle:focus {
            outline: none;
        }

        .sign-up-form .social-login-label {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            color: #1f2430;
            font-weight: 600;
            line-height: 1;
        }

        .sign-up-form .social-login-label::before,
        .sign-up-form .social-login-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e6e6e6;
        }

        .sign-up-form .social-auth-row {
            display: flex;
            gap: 12px;
            margin-top: 4px;
        }

        .sign-up-form .social-auth-btn {
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

        .sign-up-form .social-auth-btn:hover {
            border-color: #c6c6c6;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .sign-up-form .social-auth-icon {
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

        .sign-up-form .social-auth-icon.google {
            color: #1877f2;
            border: 1px solid #d7dff5;
            background: #f7faff;
            font-family: Arial, sans-serif;
        }

        .sign-up-form .social-auth-icon.facebook {
            color: #1877f2;
            border: 1px solid #d6e6ff;
            background: #f4f8ff;
            font-family: Arial, sans-serif;
        }

        .sign-up-form .social-auth-btn.disabled {
            opacity: 0.72;
            pointer-events: none;
            cursor: not-allowed;
        }

        .sign-up-form .join-now-outer a {
            color: var(--e-global-color-accent);
            font-weight: 600;
            text-decoration: none;
        }

        .sign-up-form .join-now-outer a:hover {
            color: var(--e-global-color-secondary);
            text-decoration: underline;
        }

        @media (max-width: 575px) {
            .sign-up-form .social-auth-row {
                flex-direction: column;
            }
        }
    </style>
    <!-- Sign-Up Form section-->
    <section class="login-form sign-up-form d-flex align-items-center">
        <div class="container">
            <div class="login-form-title text-center">
                <h2>Create your FREE account.</h2>
            </div>
            <div class="login-form-box">
                <div class="login-card">
                    <form method="POST" action="{{ route('customer.register.submit') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name">Your full name *</label>
                            <input class="input-field form-control" type="text" id="name" name="name"
                                value="{{ old('name') }}" placeholder="e.g. Elon Musk" autocomplete="name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Your e-mail *</label>
                            <input class="input-field form-control" type="email" id="email" name="email"
                                value="{{ old('email') }}" placeholder="e.g. elon@gmail.com" autocomplete="email">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Enter your password *</label>
                            <div class="password-wrap">
                                <input class="input-field form-control pr-5" type="password" id="password" name="password"
                                    placeholder="Password" autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="#password"
                                    data-label-show="Show" data-label-hide="Hide" aria-label="Show password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm your password *</label>
                            <div class="password-wrap">
                                <input class="input-field form-control pr-5" type="password" id="password_confirmation"
                                    name="password_confirmation" placeholder="Confirm Password" autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="#password_confirmation"
                                    data-label-show="Show" data-label-hide="Hide" aria-label="Show password confirmation">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="terms-label mt-md-3 mt-2 mb-md-4 mb-3">
                                <input class="checkbox" type="checkbox" name="terms" value="1"
                                    {{ old('terms') ? 'checked' : '' }}>
                                <span>I agree to the terms and conditions</span>
                            </label>
                            @error('terms')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary mb-0">Sign Up</button>
                        <div class="text-center my-3 social-login-label">Or continue with</div>
                        <div class="social-auth-row">
                            <a href="{{ route('customer.google.redirect', ['flow' => 'login']) }}" class="social-auth-btn">
                                <span class="social-auth-icon google">
                                    <i class="fa-brands fa-google"></i>
                                </span>
                                <span>Google</span>
                            </a>

                            {{-- <a href="#" class="social-auth-btn">
                                <span class="social-auth-icon facebook">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </span>
                                <span>Facebook</span>
                            </a> --}}
                        </div>
                    </form>
                </div>
                <div class="join-now-outer text-center">
                    <a class="mb-0" href="{{ route('user.login') }}">Already have an account? Log in</a>
                </div>
            </div>
        </div>
        <figure class="mb-0 need-layer">
            <img src="./assets/images/need-layer.png" alt="" class="img-fluid">
        </figure>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var storageKey = 'signup_password_visibility';

            document.querySelectorAll('.password-toggle').forEach(function(button) {
                var targetSelector = button.getAttribute('data-target');
                var input = document.querySelector(targetSelector);

                if (!input) {
                    return;
                }

                var savedVisibility = sessionStorage.getItem(storageKey + targetSelector);
                if (savedVisibility === 'visible') {
                    input.setAttribute('type', 'text');
                    button.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                    button.setAttribute('aria-label', 'Hide password');
                }

                button.addEventListener('click', function() {
                    var isPassword = input.getAttribute('type') === 'password';

                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    button.innerHTML = isPassword ?
                        '<i class="fa-regular fa-eye-slash"></i>' :
                        '<i class="fa-regular fa-eye"></i>';
                    button.setAttribute('aria-label', isPassword ? 'Hide password' :
                        'Show password');
                    sessionStorage.setItem(storageKey + targetSelector, isPassword ? 'visible' :
                        'hidden');
                });
            });
        });
    </script>
@endsection
