<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CustomerVerificationCodeMail;
use App\Models\Customer;
use App\Models\CustomerVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class CustomerAuthController extends Controller
{
    private const OTP_PURPOSE_EMAIL_VERIFICATION = 'email_verification';
    private const OTP_PURPOSE_RESET_PASSWORD = 'reset_password';
    private const OTP_PURPOSE_LOGIN = 'login';
    private const OTP_CHANNEL_EMAIL = 'email';
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 5;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;
    private const OTP_MAX_RESENDS = 7;

    public function showRegister()
    {
        return view('frontend.user.register');
    }

    public function showLogin()
    {
        $this->rememberCustomerRedirect(request());

        return view('frontend.user.login');
    }

    public function showForgotPassword()
    {
        return view('frontend.user.forgot-password');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $customer = DB::transaction(function () use ($request) {
            return Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'auth_provider' => 'email',
                'role' => 'customer',
                'email_verified_at' => null,
                'status' => Customer::STATUS_PENDING,
            ]);
        });

        try {
            $verificationCode = $this->issueVerificationCode($customer, $request);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('verification.notice')
                ->with([
                    'error' => 'Account created, but we could not send the verification code. Please try again.',
                    'verification_customer_id' => $customer->id,
                    'verification_email' => $customer->email,
                    'verification_purpose' => self::OTP_PURPOSE_EMAIL_VERIFICATION,
                ]);
        }

        return redirect()
            ->route('verification.notice')
            ->with([
                'success' => 'Account created. We sent a verification code to your email.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_code_id' => $verificationCode->id,
                'verification_purpose' => self::OTP_PURPOSE_EMAIL_VERIFICATION,
            ]);
    }

    public function showVerificationNotice(Request $request)
    {
        $purpose = $this->verificationPurpose($request);
        $customer = $this->resolveVerificationCustomer($request);

        if (! $customer) {
            return redirect()->route('user.login')->with('error', 'Please sign in first, then verify your code.');
        }

        if ($purpose === self::OTP_PURPOSE_EMAIL_VERIFICATION && $customer->hasVerifiedEmail()) {
            return redirect()
                ->to($this->customerRedirectUrl($request))
                ->with('success', 'Your email is already verified. Welcome back!');
        }

        $request->session()->put('verification_customer_id', $customer->id);
        $request->session()->put('verification_email', $customer->email);
        $request->session()->put('verification_purpose', $purpose);

        return view('frontend.user.verify-email', [
            'customer' => $customer,
            'purpose' => $purpose,
            'latestCode' => $this->latestPendingVerificationCode($customer, $purpose),
        ]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'otp' => ['required', 'digits:' . self::OTP_LENGTH],
            'purpose' => ['nullable', 'in:' . self::OTP_PURPOSE_EMAIL_VERIFICATION . ',' . self::OTP_PURPOSE_LOGIN . ',' . self::OTP_PURPOSE_RESET_PASSWORD],
        ]);

        $purpose = $this->verificationPurpose($request);
        $customer = Customer::find($request->integer('customer_id'));

        if (! $customer) {
            return redirect()->route('verification.notice')->with('error', 'We could not find that account.');
        }

        if ($purpose === self::OTP_PURPOSE_EMAIL_VERIFICATION && $customer->hasVerifiedEmail()) {
            return redirect()
                ->to($this->customerRedirectUrl($request))
                ->with('success', 'Your email is already verified. Welcome back!');
        }

        if ($purpose === self::OTP_PURPOSE_RESET_PASSWORD) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        }

        $verificationCode = $this->latestPendingVerificationCode($customer, $purpose);

        if (! $verificationCode) {
            return redirect()->route('verification.notice')->with([
                'error' => 'Your code expired. Please request a new one.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => $purpose,
            ]);
        }

        if ($verificationCode->attempts >= self::OTP_MAX_ATTEMPTS) {
            return redirect()->route('verification.notice')->with([
                'error' => 'Too many incorrect attempts. Please resend a new code.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => $purpose,
            ]);
        }

        if (! Hash::check($request->input('otp'), $verificationCode->code_hash)) {
            $verificationCode->forceFill([
                'attempts' => $verificationCode->attempts + 1,
            ])->save();

            $remainingAttempts = max(0, self::OTP_MAX_ATTEMPTS - $verificationCode->attempts);

            return redirect()->route('verification.notice')->with([
                'error' => $remainingAttempts > 0
                    ? "Invalid code. You have {$remainingAttempts} attempt(s) left."
                    : 'Too many incorrect attempts. Please resend a new code.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => $purpose,
            ]);
        }

        $verificationCode->forceFill([
            'consumed_at' => now(),
        ])->save();

        if ($purpose === self::OTP_PURPOSE_EMAIL_VERIFICATION) {
            $customer->markActive();
            $customer->email_verified_at = now();
            $customer->save();
        }

        if ($purpose === self::OTP_PURPOSE_RESET_PASSWORD) {
            $customer->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            $request->session()->forget([
                'verification_customer_id',
                'verification_email',
                'verification_code_id',
                'verification_purpose',
                'verification_remember',
                'password_reset_verified',
            ]);

            return redirect()->route('user.login')->with('success', 'Your password has been reset. Please log in again.');
        }

        $remember = (bool) $request->session()->pull('verification_remember', false);
        Auth::guard('customers')->login($customer, $remember);
        $customer->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        $request->session()->forget([
            'verification_customer_id',
            'verification_email',
            'verification_code_id',
            'verification_purpose',
            'verification_remember',
        ]);

        return redirect()
            ->to($this->customerRedirectUrl($request))
            ->with('success', $purpose === self::OTP_PURPOSE_LOGIN
                ? 'Login verified successfully. Welcome back!'
                : 'Your email has been verified. You are now logged in.');
    }

    public function resendEmailOtp(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'purpose' => ['nullable', 'in:' . self::OTP_PURPOSE_EMAIL_VERIFICATION . ',' . self::OTP_PURPOSE_LOGIN . ',' . self::OTP_PURPOSE_RESET_PASSWORD],
        ]);

        $purpose = $this->verificationPurpose($request);
        $customer = Customer::find($request->integer('customer_id'));

        if (! $customer) {
            return redirect()->route('verification.notice')->with('error', 'We could not find that account.');
        }

        if ($purpose === self::OTP_PURPOSE_EMAIL_VERIFICATION && $customer->hasVerifiedEmail()) {
            return redirect()
                ->to($this->customerRedirectUrl($request))
                ->with('success', 'Your email is already verified. Welcome back!');
        }

        $latestCode = $this->latestPendingVerificationCode($customer, $purpose);

        if ($latestCode && $latestCode->last_sent_at && $latestCode->last_sent_at->greaterThan(now()->subSeconds(self::OTP_RESEND_COOLDOWN_SECONDS))) {
            return redirect()->route('verification.notice')->with([
                'error' => 'Please wait before requesting another code.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => $purpose,
            ]);
        }

        if ($latestCode && $latestCode->resend_count >= self::OTP_MAX_RESENDS) {
            return redirect()->route('verification.notice')->with([
                'error' => 'You have reached the resend limit. Please contact support.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => $purpose,
            ]);
        }

        try {
            $this->issueVerificationCode($customer, $request, $purpose);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('verification.notice')->with([
                'error' => 'We could not resend the code right now. Please try again.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => $purpose,
            ]);
        }

        return redirect()->route('verification.notice')->with([
            'success' => 'A new verification code has been sent to your email.',
            'verification_customer_id' => $customer->id,
            'verification_email' => $customer->email,
            'verification_purpose' => $purpose,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'No account found with this email.');
        }

        if (! Hash::check($request->password, $customer->password ?? '')) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Wrong password.');
        }

        if (! $customer->hasVerifiedEmail()) {
            $request->session()->put('verification_customer_id', $customer->id);
            $request->session()->put('verification_email', $customer->email);
            $request->session()->put('verification_purpose', self::OTP_PURPOSE_EMAIL_VERIFICATION);

            return redirect()->route('verification.notice')->with([
                'error' => 'Please verify your email before logging in.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_purpose' => self::OTP_PURPOSE_EMAIL_VERIFICATION,
            ]);
        }

        if (! $customer->isActive() && $customer->hasVerifiedEmail()) {
            $customer->markActive()->save();
        }

        try {
            $verificationCode = $this->issueVerificationCode($customer, $request, self::OTP_PURPOSE_LOGIN);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Account found, but we could not send the login verification code. Please try again.');
        }

        $request->session()->put('verification_customer_id', $customer->id);
        $request->session()->put('verification_email', $customer->email);
        $request->session()->put('verification_purpose', self::OTP_PURPOSE_LOGIN);
        $request->session()->put('verification_remember', $request->boolean('remember'));

        return redirect()
            ->route('verification.notice')
            ->with([
                'success' => 'We sent a login verification code to your email.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_code_id' => $verificationCode->id,
                'verification_purpose' => self::OTP_PURPOSE_LOGIN,
            ]);
    }

    public function sendPasswordResetOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'No account found with this email.');
        }

        try {
            $verificationCode = $this->issueVerificationCode($customer, $request, self::OTP_PURPOSE_RESET_PASSWORD);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'We could not send the password reset code right now. Please try again.');
        }

        $request->session()->put('verification_customer_id', $customer->id);
        $request->session()->put('verification_email', $customer->email);
        $request->session()->put('verification_purpose', self::OTP_PURPOSE_RESET_PASSWORD);

        return redirect()
            ->route('verification.notice')
            ->with([
                'success' => 'We sent a password reset code to your email.',
                'verification_customer_id' => $customer->id,
                'verification_email' => $customer->email,
                'verification_code_id' => $verificationCode->id,
                'verification_purpose' => self::OTP_PURPOSE_RESET_PASSWORD,
            ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('customers')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('user.login')->with('success', 'You have been logged out.');
    }

    public function googleRedirect(Request $request)
    {
        $flow = $this->googleFlow($request);
        $request->session()->put('customer_google_flow', $flow);

        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (! $clientId || ! $redirectUri) {
            return redirect()
                ->route($this->googleFlowLandingRoute($flow))
                ->with('error', 'Google sign ' . ($flow === 'register' ? 'up' : 'in') . ' is not configured yet.');
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function googleCallback(Request $request)
    {
        $flow = $request->session()->pull('customer_google_flow', 'login');

        if (! $request->filled('code')) {
            return redirect()
                ->route($this->googleFlowLandingRoute($flow))
                ->with('error', 'Google sign ' . ($flow === 'register' ? 'up' : 'in') . ' was cancelled.');
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        if (! $clientId || ! $clientSecret || ! $redirectUri) {
            return redirect()
                ->route($this->googleFlowLandingRoute($flow))
                ->with('error', 'Google sign ' . ($flow === 'register' ? 'up' : 'in') . ' is not configured yet.');
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $request->code,
        ]);

        if (! $tokenResponse->successful()) {
            return redirect()
                ->route($this->googleFlowLandingRoute($flow))
                ->with('error', 'Unable to complete Google sign ' . ($flow === 'register' ? 'up' : 'in') . '.');
        }

        $accessToken = $tokenResponse->json('access_token');
        $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (! $profileResponse->successful()) {
            return redirect()
                ->route($this->googleFlowLandingRoute($flow))
                ->with('error', 'Unable to read your Google profile.');
        }

        $customer = $this->resolveGoogleCustomer($profileResponse->json());

        if (! $customer) {
            return redirect()
                ->route($this->googleFlowLandingRoute($flow))
                ->with('error', 'Google did not return a verified email address.');
        }

        Auth::guard('customers')->login($customer);
        $customer->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()
            ->to($this->customerRedirectUrl($request))
            ->with('success', 'Welcome back!');
    }

    private function resolveGoogleCustomer(array $googleUser): ?Customer
    {
        $googleId = (string) ($googleUser['id'] ?? '');
        $email = trim(strtolower((string) ($googleUser['email'] ?? '')));
        $isVerifiedEmail = filter_var($googleUser['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($googleId === '' || $email === '' || ! $isVerifiedEmail) {
            return null;
        }

        return DB::transaction(function () use ($googleUser, $googleId, $email) {
            $customer = Customer::query()
                ->where('auth_provider', 'google')
                ->where('provider_user_id', $googleId)
                ->first();

            if (! $customer) {
                $customer = Customer::query()
                    ->where('email', $email)
                    ->first();
            }

            if (! $customer) {
                $customer = new Customer();
            }

            $customer->name = $googleUser['name'] ?? $email ?? 'Google Customer';
            $customer->email = $email;
            $customer->profile_image = $googleUser['picture'] ?? $customer->profile_image;
            $customer->auth_provider = 'google';
            $customer->provider_user_id = $googleId;
            $customer->meta = array_merge($customer->meta ?? [], [
                'provider' => 'google',
                'google_id' => $googleId,
                'picture' => $googleUser['picture'] ?? null,
            ]);
            $customer->role = 'customer';
            $customer->markActive();
            $customer->email_verified_at = now();
            $customer->save();

            return $customer;
        });
    }

    private function issueVerificationCode(Customer $customer, Request $request, string $purpose = self::OTP_PURPOSE_EMAIL_VERIFICATION): CustomerVerificationCode
    {
        $plainCode = (string) random_int(10 ** (self::OTP_LENGTH - 1), (10 ** self::OTP_LENGTH) - 1);

        $matchingCodes = CustomerVerificationCode::query()
            ->where('customer_id', $customer->id)
            ->where('purpose', $purpose)
            ->orderByDesc('id')
            ->get();

        $verificationCode = $matchingCodes->first() ?? new CustomerVerificationCode([
            'customer_id' => $customer->id,
            'purpose' => $purpose,
        ]);

        $verificationCode->fill([
            'channel' => self::OTP_CHANNEL_EMAIL,
            'identifier' => $customer->email,
            'code_hash' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'consumed_at' => null,
            'attempts' => 0,
            'resend_count' => $verificationCode->exists ? ((int) $verificationCode->resend_count + 1) : 0,
            'last_sent_at' => now(),
            'requested_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
        ]);

        $verificationCode->save();

        try {
            $this->sendVerificationCodeEmail($customer, $plainCode, $verificationCode->expires_at->toDateTimeString(), $purpose);
        } catch (Throwable $e) {
            throw $e;
        }

        if ($matchingCodes->count() > 1) {
            CustomerVerificationCode::query()
                ->where('customer_id', $customer->id)
                ->where('purpose', $purpose)
                ->where('id', '!=', $verificationCode->id)
                ->delete();
        }

        return $verificationCode;
    }

    private function latestPendingVerificationCode(Customer $customer, string $purpose = self::OTP_PURPOSE_EMAIL_VERIFICATION): ?CustomerVerificationCode
    {
        return $customer->verificationCodes()
            ->where('purpose', $purpose)
            ->where('channel', self::OTP_CHANNEL_EMAIL)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
    }

    private function sendVerificationCodeEmail(Customer $customer, string $plainCode, string $expiresAt, string $purpose = self::OTP_PURPOSE_EMAIL_VERIFICATION): void
    {
        Mail::to($customer->email)->send(
            new CustomerVerificationCodeMail($customer, $plainCode, $expiresAt, $purpose)
        );
    }

    private function verificationPurpose(Request $request): string
    {
        $purpose = $request->input('purpose')
            ?? $request->session()->get('verification_purpose')
            ?? self::OTP_PURPOSE_EMAIL_VERIFICATION;

        return in_array($purpose, [self::OTP_PURPOSE_EMAIL_VERIFICATION, self::OTP_PURPOSE_LOGIN, self::OTP_PURPOSE_RESET_PASSWORD], true)
            ? $purpose
            : self::OTP_PURPOSE_EMAIL_VERIFICATION;
    }

    private function resolveVerificationCustomer(Request $request): ?Customer
    {
        $customerId = $request->session()->get('verification_customer_id')
            ?? $request->input('customer_id');

        if ($customerId) {
            $customer = Customer::find($customerId);

            if ($customer) {
                return $customer;
            }
        }

        $email = $request->session()->get('verification_email')
            ?? $request->input('email');

        if ($email) {
            return Customer::where('email', $email)->first();
        }

        return null;
    }

    private function rememberCustomerRedirect(Request $request): void
    {
        $previous = url()->previous();
        $current = url()->current();

        if (! $previous || $previous === $current) {
            return;
        }

        $path = parse_url($previous, PHP_URL_PATH) ?: '/';
        $query = parse_url($previous, PHP_URL_QUERY);
        $redirectUrl = $query ? $path . '?' . $query : $path;

        $ignorePaths = [
            route('user.login', [], false),
            route('user.register', [], false),
            route('customer.password.request', [], false),
            route('verification.notice', [], false),
        ];

        if (in_array($redirectUrl, $ignorePaths, true)) {
            return;
        }

        $request->session()->put('customer_intended_url', $redirectUrl);
    }

    private function customerRedirectUrl(Request $request): string
    {
        return $request->session()->pull('customer_intended_url', route('Index'));
    }

    private function googleFlow(Request $request): string
    {
        $flow = $request->input('flow')
            ?? $request->session()->get('customer_google_flow')
            ?? 'login';

        return in_array($flow, ['login', 'register'], true) ? $flow : 'login';
    }

    private function googleFlowLandingRoute(string $flow): string
    {
        return $flow === 'register' ? 'user.register' : 'user.login';
    }
}
