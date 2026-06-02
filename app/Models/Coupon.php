<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'coupon_amount',
        'attempt_time',
        'used_count',
        'expired_date',
        'is_active'
    ];
}
