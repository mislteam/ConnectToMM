<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

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
