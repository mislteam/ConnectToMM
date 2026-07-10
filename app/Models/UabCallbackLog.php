<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UabCallbackLog extends Model
{
    use HasFactory;

    protected $table = 'uab_callback_logs';

    protected $fillable = [
        'payment_transaction_id',
        'request_payload',
        'response_payload',
        'status',
        'retry_count',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'retry_count' => 'integer',
    ];

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(UabPaymentTransaction::class);
    }
}
