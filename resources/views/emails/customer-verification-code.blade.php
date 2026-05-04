<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
</head>

<body
    style="margin:0;background:linear-gradient(180deg,#f7f8fc 0%,#eef2ff 100%);font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:40px 16px;">
        <div
            style="background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 18px 40px rgba(15,23,42,0.08);">
            <div style="background:linear-gradient(135deg,#111827 0%,#374151 100%);padding:28px 32px;">
                <div
                    style="font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#cbd5e1;font-weight:700;margin-bottom:8px;">
                    Connect To Myanmar
                </div>
                <h1 style="margin:0;font-size:28px;line-height:1.2;color:#ffffff;">
                    {{ ($purpose ?? 'email_verification') === 'login'
                        ? 'Verify your login'
                        : (($purpose ?? 'email_verification') === 'reset_password'
                            ? 'Reset your password'
                            : 'Verify your email address') }}
                </h1>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#374151;">
                    Hello {{ $customer->name ?? 'there' }},
                </p>

                <p style="margin:0 0 22px;font-size:16px;line-height:1.7;color:#374151;">
                    Use this 6-digit code to
                    {{ ($purpose ?? 'email_verification') === 'login'
                        ? 'complete your login'
                        : (($purpose ?? 'email_verification') === 'reset_password'
                            ? 'reset your password'
                            : 'verify your email address') }}:
                </p>

                <div style="text-align:center;margin:0 0 24px;">
                    <div
                        style="display:inline-block;padding:18px 26px;border-radius:16px;background:#f8fafc;border:1px solid #dbe2ea;">
                        <span
                            style="display:block;font-size:15px;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;margin-bottom:10px;">
                            Verification code
                        </span>
                        <strong
                            style="display:block;font-size:34px;line-height:1.1;letter-spacing:10px;font-weight:800;color:#111827;">
                            {{ $code }}
                        </strong>
                    </div>
                </div>

                <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#6b7280;">
                    This code expires at {{ $expiresAt }}.
                </p>

                <p style="margin:0;font-size:14px;line-height:1.6;color:#6b7280;">
                    If you did not request this code, you can ignore this email.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
