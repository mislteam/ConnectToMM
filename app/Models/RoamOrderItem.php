<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoamOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'roam_order_id',
        'data',
        'iccid',
        'mobile_number',
        'activation_code',
        'sm_dp_address',
        'apn',
        'dp_id',
        'validity',
        'used_mb',
        'activate_before',
        'start_date',
        'end_date',
        'pdf_url',
        'raw_card_data',
    ];

    protected $casts = [
        'validity' => 'integer',
        'used_mb' => 'decimal:2',
        'activate_before' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'raw_card_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RoamOrder::class, 'roam_order_id');
    }
}
