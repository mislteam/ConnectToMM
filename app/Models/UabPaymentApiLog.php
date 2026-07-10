<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UabPaymentApiLog extends Model
{
    use HasFactory;

    protected $table = 'uab_payment_api_logs';

    protected $fillable = [
        'payment_transaction_id',
        'endpoint',
        'http_method',
        'request_payload',
        'response_payload',
        'status_code',
        'execution_time',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'status_code' => 'integer',
        'execution_time' => 'integer',
    ];

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(UabPaymentTransaction::class);
    }
}
