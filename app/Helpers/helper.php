<?php


// Bannner Image
if (!function_exists('get_banner')) {
    function get_banner($type)
    {
        $banner =  \App\Models\Banner::where('banner_type', $type)->first();
        if ($banner) {
            return $banner->image;
        }
    }
}


if (!function_exists('get_section')) {
    function get_section($key)
    {
        $section = \App\Models\Section::where('section_key', $key)->first();
        return $section;
    }
}

if (!function_exists('image_delete')) {
    function image_delete($path, $old_image)
    {
        if ($old_image) {
            $oldPath = public_path($path . $old_image);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
    }
}

if (!function_exists('store_image')) {
    function store_image($file, $folder)
    {
        $file_name = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path($folder), $file_name);
        return $file_name;
    }
}

if (!function_exists('section_keys_by_page')) {
    function section_keys_by_page(string $page)
    {
        return collect(config('sections'))
            ->filter(fn($section) => $section['page'] === $page)
            ->keys()
            ->values()
            ->toArray();
    }
}

if (!function_exists('payment_setting_id_for_method')) {
    function payment_setting_id_for_method(?string $paymentMethod): ?int
    {
        return match ($paymentMethod) {
            'direct_bank_transfer' => 1,
            'uabpay', 'uab_pay', 'UAB Pay' => 2,
            'wallet' => 3,
            default => null,
        };
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(?string $paymentMethod): ?string
    {
        if ($paymentMethod === null || trim($paymentMethod) === '') {
            return null;
        }

        $settingId = payment_setting_id_for_method($paymentMethod);

        if ($settingId !== null) {
            static $paymentSettings = [];

            if (!array_key_exists($settingId, $paymentSettings)) {
                $paymentSettings[$settingId] = \App\Models\PaymentSetting::query()->find($settingId);
            }

            $paymentSetting = $paymentSettings[$settingId];

            if ($paymentSetting?->type) {
                return $paymentSetting->type;
            }
        }

        if ($paymentMethod === 'wallet') {
            return 'Wallet';
        }

        return \Illuminate\Support\Str::headline($paymentMethod);
    }
}

if (!function_exists('uab_payment_method_labels')) {
    function uab_payment_method_labels(?string $paymentMethods): array
    {
        if ($paymentMethods === null || trim($paymentMethods) === '') {
            return [];
        }

        return collect(explode(',', $paymentMethods))
            ->map(fn($method) => trim((string) $method))
            ->filter()
            ->map(function (string $method): string {
                $gatewayMethod = \App\Payment\Providers\Uab\Enums\PaymentMethod::tryFrom($method);

                return $gatewayMethod?->label() ?? \Illuminate\Support\Str::headline($method);
            })
            ->unique()
            ->values()
            ->all();
    }
}

if (!function_exists('uab_selected_payment_method_label')) {
    function uab_selected_payment_method_label(
        ?string $paymentMethod,
        ?string $paymentType = null,
        ?string $cardType = null
    ): ?string {
        $paymentMethod = trim((string) $paymentMethod);
        $paymentType = trim((string) $paymentType);
        $cardType = trim((string) $cardType);

        if ($cardType !== '') {
            return match (strtolower($cardType)) {
                'master', 'mastercard' => 'Master Card',
                'visa' => 'Visa',
                'unionpay', 'union pay' => 'UnionPay',
                'mpu' => 'MPU',
                default => \Illuminate\Support\Str::headline($cardType),
            };
        }

        if ($paymentMethod !== '') {
            $gatewayMethod = \App\Payment\Providers\Uab\Enums\PaymentMethod::tryFrom($paymentMethod);

            if ($gatewayMethod !== null) {
                return $gatewayMethod->label();
            }

            return match (strtolower($paymentMethod)) {
                'uab bank', 'uab_bank' => 'UAB Bank',
                default => \Illuminate\Support\Str::headline($paymentMethod),
            };
        }

        return $paymentType !== '' ? \Illuminate\Support\Str::headline($paymentType) : null;
    }
}

if (!function_exists('uab_transaction_selected_payment_label')) {
    function uab_transaction_selected_payment_label(?string $outerOrderId): ?string
    {
        if ($outerOrderId === null || trim($outerOrderId) === '') {
            return null;
        }

        static $labels = [];
        $outerOrderId = trim($outerOrderId);

        if (array_key_exists($outerOrderId, $labels)) {
            return $labels[$outerOrderId];
        }

        $transaction = \App\Models\UabPaymentTransaction::query()
            ->where('merchant_reference', $outerOrderId)
            ->latest('id')
            ->first();

        if ($transaction === null) {
            return $labels[$outerOrderId] = null;
        }

        $paymentMethod = $transaction->selected_payment_method;
        $paymentType = $transaction->selected_payment_type;
        $cardType = $transaction->selected_card_type;

        if (!$paymentMethod && !$paymentType && !$cardType) {
            $notifyPayload = (array) data_get($transaction->provider_response, 'notify', []);
            $paymentMethod = $notifyPayload['PaymentMethod'] ?? null;
            $paymentType = $notifyPayload['PaymentType'] ?? null;
            $cardType = $notifyPayload['CardType'] ?? null;
        }

        return $labels[$outerOrderId] = uab_selected_payment_method_label(
            is_string($paymentMethod) ? $paymentMethod : null,
            is_string($paymentType) ? $paymentType : null,
            is_string($cardType) ? $cardType : null,
        );
    }
}

if (!function_exists('payment_method_display_label')) {
    function payment_method_display_label(?string $paymentMethod, ?string $outerOrderId = null): ?string
    {
        $baseLabel = payment_method_label($paymentMethod);

        if (in_array($paymentMethod, ['uabpay', 'uab_pay', 'UAB Pay'], true)) {
            $selectedLabel = uab_transaction_selected_payment_label($outerOrderId);

            if ($selectedLabel) {
                return ($baseLabel ?: 'Online Payment') . ' (' . $selectedLabel . ')';
            }
        }

        return $baseLabel;
    }
}

if (!function_exists('parseProductName')) {

    function parseProductName($name)
    {
        $name = trim((string) $name);

        $result = [
            'product_name' => $name,
            'data' => null,
            'traffic_type' => null,
            'service_day' => null,
        ];

        //     if (preg_match('/(\d+)\s*[-]?\s*days?/i', $name, $dayMatch)) {

        //         $result['service_day'] = (int)$dayMatch[1] . 'day';
        //     }

        //      // /day style (500MB/day → daily plan)
        //    if (preg_match('/\/\s*day\b/i', $name)) {
        //         $result['service_day'] = 'day';
        //     }

        //     // (Charge from 3 days)
        //    if (preg_match('/\(([^)]*Charge\s*from\s*[^)]*)\)/i', $name, $cMatch)) {
        //         $result['service_day'] = '(' . trim($cMatch[1]) . ')';
        //     }

        if (preg_match('/(\d+)\s*[-]?\s*days?/i', $name, $dayMatch)) {

            $result['service_day'] = (int)$dayMatch[1] . 'day';
        } elseif (preg_match('/\/\s*day\b/i', $name)) {

            $result['service_day'] = 'day';
        }

        if (preg_match('/\(([^)]*Charge\s*from\s*[^)]*)\)/i', $name, $cMatch)) {

            $result['service_day'] = '(' . trim($cMatch[1]) . ')';
        }



        // 500MB / 22GB / 500MB/day
        if (preg_match('/(\d+(?:\.\d+)?)\s*(MB|GB)(?:\s*\/\s*day)?/i', $name, $dataMatch)) {

            $result['data'] =
                $dataMatch[1] . strtoupper($dataMatch[2]);
        } elseif (preg_match('/unlimited\s+data/i', $name)) {

            $result['data'] = 'Unlimited Data';
        } elseif (preg_match('/\bunlimited(?:\s*\/\s*day)?\b/i', $name)) {

            $result['data'] = 'Unlimited';
        } elseif (preg_match('/\b(\d+\s*MAX|MAX)\b/i', $name, $maxMatch)) {

            $result['data'] = 'Unlimited (' . strtoupper(str_replace(' ', '', $maxMatch[1])) . ')';
        } elseif (preg_match('/full\s+(?:unlimited|speed)/i', $name)) {

            $result['data'] = 'Unlimited';
        }

        // data
        // if (preg_match('/(\d+(?:\.\d+)?)\s*(MB|GB)(?:\s*\/\s*day)?/i', $name, $dataMatch)) {

        //     $result['data'] = $dataMatch[1] . strtoupper($dataMatch[2]);

        // } elseif (preg_match('/unlimited\s+data/i', $name)) {

        //     $result['data'] = 'Unlimited Data';

        // } elseif (preg_match('/\((\d*MAX)\)/i', $name, $maxMatch)) {

        //     $maxValue = strtoupper($maxMatch[1]);

        //     $result['data'] = 'Unlimited (' . $maxValue . ')';

        // }

        // if (preg_match('/\/\s*day|days|daily/i', $name)) {
        //     $result['traffic_type'] = 'daily';
        // } elseif (preg_match('/unlimited|MAX|max/i', $name)) {
        //     $result['traffic_type'] = 'unlimited';
        // } elseif (preg_match('/total/i', $name)) {


        //     $result['traffic_type'] = 'total';
        // }

        if (preg_match('/\btotal\b/i', $name)) {

            $result['traffic_type'] = 'total';
        } elseif (preg_match('/\bunlimited\b|\bmax\b|MAX|\bspeed\b/i', $name)) {

            $result['traffic_type'] = 'unlimited';
        } elseif (preg_match('/\/\s*day|daily|DAY/i', $name)) {

            $result['traffic_type'] = 'daily';
        }


        $cleanName = preg_replace([
            '/^\s*\[[^\]]+\]\s*/i',
            '/\s*-\s*\d+\s*days?\b.*$/i',
            '/\s*-\s*(?:Total\s+\d+(?:\.\d+)?\s*(?:MB|GB)|\d+\s*(?:MB|GB|MAX)?\s*\/\s*day|Unlimited\s*\/\s*day|\d+\s*days?)\b.*$/i',
            '/\s*\([^)]*(?:\d+(?:\.\d+)?\s*(?:MB|GB)|unlimited\s+data|total|\/\s*day)[^)]*\)\s*$/i',
            '/\s*-\s*\([^)]*(?:\d+(?:\.\d+)?\s*(?:MB|GB)|unlimited\s+data|total|\/\s*day)[^)]*\)\s*$/i',
            '/\s*\([^)]*(?:MB|GB|MAX|day|daily|charge|total)[^)]*\)\s*/i',
        ], '', $name);

        $result['product_name'] = trim($cleanName, " \t\n\r\0\x0B-");

        return $result;
    }
}

if (!function_exists('getOrderTypes')) {
    function getOrderTypes(string $settingName, string $type)
    {
        $rawOrderTypes = \App\Models\GeneralSetting::where('name', $settingName)->first()?->value;
        $orderTypes = json_decode($rawOrderTypes, true);

        $tabs = [];
        if (in_array("{$type}_new", $orderTypes)) {
            $tabs["new_{$type}"] = [
                'label' => $type === 'physical' ? 'New SIM' : 'New eSIM'
            ];
        }

        if (in_array("{$type}_recharge", $orderTypes)) {
            $tabs["recharge_{$type}"] = ['label' => 'Recharge'];
        }

        return $tabs;
    }
}

if (!function_exists('getUsdPrice')) {
    function getUsdPrice(float|int $totalMMK, string $rateName)
    {
        $usdRate = \App\Models\Currency::query()->where('name', $rateName)->value('value');

        if (! is_numeric($usdRate) || (float) $usdRate <= 0) {
            return null;
        }

        return round((float)$totalMMK / (float)$usdRate, 2);
    }
}

if (! function_exists('displayPrice')) {
    function displayPrice(int|float $mmkPrice, string $rateName = "user_usd_rate"): string
    {
        $currency = session('currency', config('currency.default'));
        if (!in_array($currency, config('currency.supported'))) {
            $currency = config('currency.default');
        }

        if ($currency === "USD") {
            $usdPrice = getUsdPrice($mmkPrice, $rateName);
            if ($usdPrice == null) {
                return number_format($mmkPrice) . ' MMK';
            }

            return '$' . number_format($usdPrice, 2);
        }

        return number_format($mmkPrice) . ' MMK';
    }
}

if (!function_exists('banner')) {
    function banner(string $key)
    {
        return \App\Models\Banner::where('banner_type', $key)->first();
    }
}
