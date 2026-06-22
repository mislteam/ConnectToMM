<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoamCoupon extends Model
{
    use HasFactory;

    protected $casts = [
        'plans' => 'array',
    ];

    protected $fillable = [
        'code',
        'plans',
        'discount_percentage',
        'attempt_time',
        'used_count',
        'expired_date',
        'is_active'
    ];
}
