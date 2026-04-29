{{ ($purpose ?? 'email_verification') === 'login'
    ? 'Verify your login'
    : (($purpose ?? 'email_verification') === 'reset_password'
        ? 'Reset your password'
        : 'Verify your email address') }}

Hello {{ $customer->name ?? 'there' }},

Use this 6-digit code to
{{ ($purpose ?? 'email_verification') === 'login'
    ? 'complete your login'
    : (($purpose ?? 'email_verification') === 'reset_password'
        ? 'reset your password'
        : 'verify your email address') }}:

{{ $code }}

This code expires at {{ $expiresAt }}.

If you did not request this code, you can ignore this email.
