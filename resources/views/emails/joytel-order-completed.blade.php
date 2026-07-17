@php
    $items = collect($payload['joytel_items'] ?? []);
    $customerName = $notifiable->name ?? 'Customer';
    $customerEmail = $payload['customer_email'] ?? ($notifiable->email ?? '-');
    $orderReference = $payload['reference'] ?? '-';
    $joytelReference = $payload['joytel_order_num'] ?? '-';
    $orderTime = $payload['order_created_at'] ?? now()->format('Y/m/d H:i:s');

    $splitLpa = function (?string $code): array {
        $code = trim((string) $code);

        if (preg_match('/^LPA:1\\$([^$]+)\\$(.+)$/', $code, $matches)) {
            return [$matches[1], $matches[2]];
        }

        return ['', $code];
    };
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $payload['mail_subject'] ?? 'Joytel order completed' }}</title>
</head>

<body style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#333333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="background:#f4f7fb;margin:0;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="720" cellspacing="0" cellpadding="0"
                    style="width:720px;max-width:94%;background:#ffffff;border:1px solid #dddddd;border-radius:6px;overflow:hidden;">
                    <tr>
                        <td style="padding:18px 24px;border-bottom:1px solid #e5e5e5;">
                            <div style="font-size:18px;font-weight:700;color:#222222;">
                                {{ $items->first()['sale_plan_name'] ?? ($items->first()['product_code'] ?? 'Joytel eSIM') }}
                            </div>
                            <div style="font-size:13px;color:#777777;margin-top:3px;">Connect To Myanmar</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 28px 18px;">
                            <h2 style="font-size:18px;margin:0 0 20px;color:#333333;">Dear {{ $customerName }} :</h2>
                            <p style="font-size:14px;line-height:1.55;margin:0 0 14px;">
                                Thank you for choosing our eSIM service.<br>
                                Below is your order information, including the purchased eSIM QR Code.<br>
                                Please follow the instructions to install.
                            </p>
                            <p style="font-size:14px;line-height:1.55;margin:0 0 14px;">
                                When you arrive at destination, please enable the installed eSIM and turn on Data
                                Roaming on your cell phone settings, then enjoy a fast and stable Internet connection.
                            </p>
                            <p style="font-size:14px;line-height:1.55;margin:0 0 14px;">
                                <strong>WARNING!</strong> Most eSIMs can only be installed on the same device. Once
                                installed on one device, you can not install it again on another device even if it is
                                removed/deleted from the first device.
                            </p>
                            <p style="font-size:14px;line-height:1.55;margin:0 0 24px;">
                                <strong>NOTE:</strong> Please do not reply to this email. For any issue, please contact
                                our customer service.
                            </p>

                            @foreach ($items as $index => $item)
                                @php
                                    [$smdpAddress, $activationCode] = $splitLpa($item['qrcode'] ?? null);
                                    $qrPath = $item['plain_qr_path'] ?? null;
                                    $qrFullPath = $qrPath ? storage_path('app/public/' . $qrPath) : null;
                                    if ($qrFullPath && !is_file($qrFullPath)) {
                                        $qrFullPath = storage_path('app/' . $qrPath);
                                    }
                                    $qrSrc =
                                        $qrFullPath && is_file($qrFullPath) && isset($message)
                                            ? $message->embed($qrFullPath)
                                            : null;
                                @endphp

                                @if ($index > 0)
                                    <div style="height:22px;"></div>
                                @endif

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                    style="border-collapse:collapse;background:#f5f5f5;border:1px solid #e2e2e2;">
                                    <tr>
                                        <td colspan="2"
                                            style="padding:12px 16px;font-size:14px;font-weight:700;background:#eeeeee;border-bottom:1px solid #dddddd;">
                                            eSIM {{ $index + 1 }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:58%;vertical-align:top;">
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                                style="border-collapse:collapse;">
                                                @php
                                                    $detailRows = [
                                                        'Order ID' => $joytelReference,
                                                        'Email Address' => $customerEmail,
                                                        'Order Time(UTC+0)' => $orderTime,
                                                        'Package Name' =>
                                                            $item['sale_plan_name'] ??
                                                            ($item['product_code'] ?? 'Joytel eSIM'),
                                                        'Validation' => !empty($item['sale_plan_days'])
                                                            ? $item['sale_plan_days'] . ' days'
                                                            : '-',
                                                        'SM-DP+ Address' => $smdpAddress ?: '-',
                                                        'Activation Code' => $activationCode ?: '-',
                                                        'SN Code' => $item['sn_code'] ?? '-',
                                                        'CID' => $item['cid'] ?? '-',
                                                    ];

                                                    foreach (
                                                        [
                                                            'PIN 1' => $item['pin1'] ?? null,
                                                            'PIN 2' => $item['pin2'] ?? null,
                                                            'PUK 1' => $item['puk1'] ?? null,
                                                            'PUK 2' => $item['puk2'] ?? null,
                                                        ]
                                                        as $label => $value
                                                    ) {
                                                        if ($value !== null && $value !== '') {
                                                            $detailRows[$label] = $value;
                                                        }
                                                    }
                                                @endphp

                                                @foreach ($detailRows as $label => $value)
                                                    <tr>
                                                        <td
                                                            style="width:38%;padding:10px 16px;font-size:12px;color:#555555;border-bottom:1px solid #e2e2e2;">
                                                            {{ $label }}
                                                        </td>
                                                        <td
                                                            style="padding:10px 16px;font-size:12px;color:#333333;border-bottom:1px solid #e2e2e2;word-break:break-word;">
                                                            {{ $value }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                        <td align="center"
                                            style="width:42%;vertical-align:middle;padding:18px;background:#f8f8f8;border-left:1px solid #e2e2e2;">
                                            <div style="font-size:12px;color:#555555;margin-bottom:10px;">QR Code</div>
                                            @if ($qrSrc)
                                                <img src="{{ $qrSrc }}" alt="Joytel eSIM QR Code"
                                                    style="display:block;width:220px;max-width:100%;height:auto;border:0;">
                                            @else
                                                <div style="font-size:12px;color:#777777;">QR image unavailable</div>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"
                                            style="padding:10px 16px;font-size:12px;color:#333333;border-top:1px solid #e2e2e2;word-break:break-word;">
                                            <strong>SM-DP+ Address and Activation Code:</strong><br>
                                            {{ $item['qrcode'] ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"
                                            style="padding:10px 16px;font-size:12px;color:#555555;background:#f7f7f7;">
                                            <strong>Description:</strong><br>
                                            The eSIM must be installed and activated within 30 days, or it will become
                                            unusable and non-refundable.
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <div style="font-size:13px;line-height:1.55;margin-top:24px;">
                                <p style="margin:0 0 10px;">
                                    iPhone users with iOS 17.4 or later and you receive a QR code, press and hold the QR
                                    code, then tap Add eSIM option displayed at the bottom of the menu.
                                </p>

                                @if (!empty($payload['url']))
                                    <p style="margin:0 0 18px;">
                                        <a href="{{ $payload['url'] }}"
                                            style="color:#165dcc;text-decoration:underline;">
                                            View your order details
                                        </a>
                                    </p>
                                @endif

                                <h3 style="font-size:15px;margin:18px 0 8px;">eSIM Settings Instructions</h3>
                                <p style="margin:0 0 6px;"><strong>iOS Devices:</strong></p>
                                <ol style="margin:0 0 14px 18px;padding:0;">
                                    <li>Go to Settings &gt; Cellular/Mobile Data &gt; Add eSIM/Add Cellular Plan.</li>
                                    <li>Choose Use QR code, scan the code, then follow the on-screen steps.</li>
                                    <li>After activation, enable this line and turn on Data Roaming.</li>
                                </ol>

                                <p style="margin:0 0 6px;"><strong>Google Devices:</strong></p>
                                <ol style="margin:0 0 14px 18px;padding:0;">

                                    <li>Go to Settings &gt; Network &amp; internet &gt; SIMs.</li>
                                    <li>Choose Download a SIM instead, scan the QR code, then download.</li>
                                    <li>After download, turn on the eSIM and Roaming.</li>
                                </ol>

                                <p style="margin:0 0 6px;"><strong>Samsung Devices:</strong></p>
                                <ol style="margin:0;padding:0 0 0 18px;">
                                    <li>Go to Settings &gt; Connections &gt; SIM manager &gt; Add eSIM.</li>
                                    <li>Choose Scan QR code, then scan the code or enter activation code.</li>
                                    <li>Turn on the eSIM and Mobile data.</li>
                                </ol>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
