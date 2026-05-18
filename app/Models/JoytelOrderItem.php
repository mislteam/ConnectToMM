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
        'line_no',
        'product_code',
        'product_name',
        'quantity',
        'service_day',
        'days',
        'unit_price',
        'total_price',
        'line_total',
        'sn_code',
        'sn_pin',
        'coupon',
        'qrcode_type',
        'qrcode',
        'cid',
        'eid',
        'profile_type',
        'sale_plan_name',
        'sale_plan_days',
        'pin1',
        'pin2',
        'puk1',
        'puk2',
        'rsp_order_id',
        'rsp_tid',
        'outbound_code',
        'product_expire_date',
        'status',
        'request_payload',
        'response_payload',
        'callback_payload',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'callback_payload' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(JoytelOrder::class, 'joytel_order_id');
    }
}
