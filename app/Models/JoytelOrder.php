<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoytelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'customer_id',
        'service_type',
        'channel_type',
        'customer_code',
        'order_tid',
        'joytel_order_code',
        'warehouse',
        'submit_type',
        'reply_type',
        'receive_name',
        'phone',
        'email',
        'remark',
        'request_signature',
        'status',
        'remote_status_code',
        'remote_status_label',
        'submitted_at',
        'acknowledged_at',
        'callback_received_at',
        'completed_at',
        'cancelled_at',
        'last_synced_at',
        'request_payload',
        'response_payload',
        'callback_payload',
        'query_payload',
        'meta',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'callback_payload' => 'array',
        'query_payload' => 'array',
        'meta' => 'array',
        'submitted_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'callback_received_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JoytelOrderItem::class);
    }
}
