<?php

namespace App\Payment\Providers\Uab\Enums;

enum PaymentMethod: string
{
    case UABPAY = 'uabpay';
    case VISA_MASTER = 'visa_master';
    case UPI = 'upi';
    case MPU = 'mpu';
    case MMQR = 'mmqr';

    public function label(): string
    {
        return match ($this) {
            self::UABPAY => 'UAB Pay',
            self::VISA_MASTER => 'Visa / Master',
            self::UPI => 'UPI',
            self::MPU => 'MPU',
            self::MMQR => 'MMQR',
        };
    }

    public static function gatewayOptions(): string
    {
        return implode(',', array_map(
            static fn (self $method): string => $method->value,
            self::cases()
        ));
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $method): string => $method->value,
            self::cases()
        );
    }
}
