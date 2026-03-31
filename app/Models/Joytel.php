<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Joytel extends Model
{
    use HasFactory;

    protected $casts = [
        'plan' => 'array',
        'photo' => 'array',
        'usage_location' => 'array'
    ];

    protected $fillable = [
        'category_name',
        'product_name',
        'usage_location',
        'supplier',
        'product_type',
        'plan',
        'expired_date',
        'photo',
        'activation_policy',
        'delivery_time',
        'status'
    ];
}
