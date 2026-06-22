<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

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
        return $this->belongsTo(UabCredential::class, 'payment_setting_id');
    }
}
