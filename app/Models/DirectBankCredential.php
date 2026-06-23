<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectBankCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_setting_id',
        'bank_name',
        'account_name',
        'account_number'
    ];

    public function paymentSetting()
    {
        return $this->belongsTo(PaymentSetting::class, 'payment_setting_id');
    }
}
