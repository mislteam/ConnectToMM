<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UabCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_setting_id',
        'channel',
        'payment_methods',
        'merchant_user_id',
        'api_url',
        'base_url',
        'client_id',
        'access_key',
        'secret_key',
        'client_secret',
        'merchant_id',
        'ins_id',
        'notify_url',
        'success_url',
        'cancel_url',
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_postal_code',
        'billing_state',
        'billing_country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function paymentSetting()
    {
        return $this->belongsTo(PaymentSetting::class, 'payment_setting_id');
    }
}
