<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoytelOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'joytel_order_id',
        'product_code',
        'sn_code',
        'sn_pin',
        'cid',
        'qrcode_type',
        'qrcode',
        'pin1',
        'pin2',
        'puk1',
        'puk2',
        'sale_plan_name',
        'sale_plan_days',
        'product_expire_date',
        'esim_status',
        'profile_state',
        'eid',
        'used_bytes',
        'total_usage_bytes',
        'activation_time',
        'expiration_time',
        'raw_usage_data',
        'raw_callback_data',
    ];

    protected $casts = [
        'qrcode_type' => 'integer',
        'sale_plan_days' => 'integer',
        'product_expire_date' => 'date',
        'esim_status' => 'integer',
        'used_bytes' => 'integer',
        'total_usage_bytes' => 'integer',
        'activation_time' => 'datetime',
        'expiration_time' => 'datetime',
        'raw_usage_data' => 'array',
        'raw_callback_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(JoytelOrder::class, 'joytel_order_id');
    }
}
