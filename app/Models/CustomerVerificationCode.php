<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'purpose',
        'channel',
        'identifier',
        'code_hash',
        'expires_at',
        'consumed_at',
        'attempts',
        'resend_count',
        'last_sent_at',
        'requested_ip',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
