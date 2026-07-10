<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UabGatewayToken extends Model
{
    use HasFactory;

    protected $table = 'uab_gateway_tokens';

    protected $fillable = [
        'access_token',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];
}
