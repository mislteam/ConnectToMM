<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    public const DIRECT_BANK_TRANSFER_ID = 1;
    public const ONLINE_PAYMENT_ID = 2;
    public const WALLET_ID = 3;

    protected $table = 'payment_setting';

    protected $fillable = [
        'type',
        'status'
    ];

    public function directBankCredentials()
    {
        return $this->hasMany(DirectBankCredential::class, 'payment_setting_id');
    }

    public function uabCredential()
    {
        return $this->hasOne(UabCredential::class, 'payment_setting_id');
    }
}
