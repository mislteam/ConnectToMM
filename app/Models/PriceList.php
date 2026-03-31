<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_code',
        'plan',
        'exchange_rate',
        'dp_status',
        'dp_info'
    ];
}