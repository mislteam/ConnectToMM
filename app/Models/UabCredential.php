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
        'merchant_user_id',
        'api_url',
        'access_key',
        'secret_key',
        'client_secret',
    ];

    public function paymentSetting()
    {
        return $this->belongsTo(PaymentSetting::class, 'payment_setting_id');
    }
}
