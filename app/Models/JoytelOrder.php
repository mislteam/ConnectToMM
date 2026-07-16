<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JoytelOrder extends Model
{
    use HasFactory, SoftDeletes;

    public const OUR_STATUS_ORDER_START = 0;
    public const OUR_STATUS_PENDING_PAYMENT = 1;
    public const OUR_STATUS_PAID = 2;
    public const OUR_STATUS_API_PROCESSING = 3;
    public const OUR_STATUS_API_SUCCESS = 4;
    public const OUR_STATUS_API_FAILED = 5;
    public const OUR_STATUS_COMPLETED = 6;
    public const OUR_STATUS_CANCELLED = 7;
    public const OUR_STATUS_REFUNDED = 8;
    public const OUR_STATUS_ADMIN_CANCELLED = 9;

    

    public const CUSTOMER_STATUS_LABELS = [
        self::OUR_STATUS_ORDER_START => 'Order Started',
        self::OUR_STATUS_PENDING_PAYMENT => 'Pending Payment',
        self::OUR_STATUS_PAID => 'Paid',
        self::OUR_STATUS_API_PROCESSING => 'Processing',
        self::OUR_STATUS_API_SUCCESS => 'Success',
        self::OUR_STATUS_API_FAILED => 'Failed',
        self::OUR_STATUS_COMPLETED => 'Completed',
        self::OUR_STATUS_CANCELLED => 'Cancelled',
        self::OUR_STATUS_REFUNDED => 'Refunded',
        self::OUR_STATUS_ADMIN_CANCELLED => 'Admin Cancel',
    ];

    protected $fillable = [
        'customer_id',
        'joytel_order_num',
        'outer_order_id',
        'product_name',
        'service_type',
        'order_type',
        'source_sn_code',
        'quantity',
        'unit_price',
        'total_price',
        'payment_method',
        'coupon_id',
        'discount_amount',
        'validity_days',
        'start_date',
        'end_date',
        'our_status',
        'joytel_status',
        'renewal',
        'main_order_num',
        'remark',
        'is_send_email',
        'purchase_date',
        'callback_received_at',
        'raw_response',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'payment_method' => 'string',
        'discount_amount' => 'integer',
        'validity_days' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'our_status' => 'integer',
        'joytel_status' => 'integer',
        'renewal' => 'boolean',
        'is_send_email' => 'boolean',
        'purchase_date' => 'datetime',
        'callback_received_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function getCustomerStatusLabelAttribute(): string
    {
        return self::CUSTOMER_STATUS_LABELS[(int) $this->our_status]
            ?? (string) $this->our_status;
    }

    public function getBillableTotalPriceAttribute(): float
    {
        $unitPrice = (float) ($this->unit_price ?? 0);
        $quantity = max(1, (int) ($this->quantity ?? 1));

        if ($unitPrice > 0) {
            return $unitPrice * $quantity;
        }

        return (float) ($this->total_price ?? 0);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JoytelOrderItem::class);
    }
}
