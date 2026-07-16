<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoytelCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'product_names',
        'discount_percentage',
        'attempt_time',
        'used_count',
        'expired_date',
        'is_active'
    ];

    
}
