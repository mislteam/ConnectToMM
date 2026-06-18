<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoytelPhysical extends Model
{
    use HasFactory;

    protected $casts = [
        'price' => 'decimal:2',
        'coverage' => 'array',
        'photo' => 'array',
    ];

    protected $fillable = [
        'product_name',
        'data',
        'traffic_type',
        'service_day',
        'price',
        'code',
        'coverage',
        'type',
        'product_description',
        'memo',
        'activation_type',
        'provider',
        'network',
        'hotspot',
        'recharge',
        'photo',
        'status'
    ];
}
