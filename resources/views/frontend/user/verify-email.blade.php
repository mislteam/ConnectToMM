@extends('frontend.layouts.index')
@section('title', ($purpose ?? 'email_verification') === 'login'
    ? 'Verify Login'
    : (($purpose ?? 'email_verification') === 'reset_password' ? 'Reset Password' : 'Verify Email'))
@section('content')
    @include('components.alert')
    <section class="login-form sign-up-form d-flex align-items-center">
        <div class="container">
            <div class="login-form-title text-center">
                <h2>
                    {{ ($purpose ?? 'email_verification') === 'login'
                        ? 'Verify your login'
                        : (($purpose ?? 'email_verification') === 'reset_password'
                            ? 'Reset your password'
                            : 'Verify your email') }}
                </h2>
                <p class="text-muted">
                    We sent a 6-digit code to
                    <strong>{{ $customer->email ?? session('verification_email') }}</strong>.
                    {{ ($purpose ?? 'email_verification') === 'login'
                        ? 'Enter it below to finish signing in.'
                        : (($purpose ?? 'email_verification') === 'reset_password'
                            ? 'Enter it below, then choose a new password.'
                            : 'Enter it below to continue.') }}
                </p>
            </div>

            <div class="login-form-box">
                <div class="login-card">
                    <form method="POST" action="{{ route('verification.verify.otp') }}">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id ?? session('verification_customer_id') }}">
                        <input type="hidden" name="purpose" value="{{ $purpose ?? session('verification_purpose', 'email_verification') }}">

                        <div class="form-group">
                            <label for="otp">Verification code</label>
                            <input class="input-field form-control" type="text" id="otp" name="otp"
                                inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                                placeholder="Enter 6-digit code" value="{{ old('otp') }}">
                            @error('otp')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        @if (($purpose ?? session('verification_purpose', 'email_verification')) === 'reset_password')
                            <div class="form-group">
                                <label for="password">New password</label>
                                <input class="input-field form-control" type="password" id="password" name="password"
                                    placeholder="New password" autocomplete="new-password">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirm new password</label>
                                <input class="input-field form-control" type="password" id="password_confirmation"
                                    name="password_confirmation" placeholder="Confirm new password"
                                    autocomplete="new-password">
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100">
                            {{ ($purpose ?? session('verification_purpose', 'email_verification')) === 'reset_password'
                                ? 'Reset Password'
                                : 'Verify OTP' }}
                        </button>
                    </form>

                    @if (($purpose ?? session('verification_purpose', 'email_verification')) !== 'reset_password')
                        <form method="POST" action="{{ route('verification.resend.otp') }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $customer->id ?? session('verification_customer_id') }}">
                            <input type="hidden" name="purpose" value="{{ $purpose ?? session('verification_purpose', 'email_verification') }}">
                            <button type="submit" class="btn btn-outline-dark w-100">Resend code</button>
                        </form>
                    @endif

                    <div class="text-center mt-3">
                        <a href="{{ route('user.login') }}">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
