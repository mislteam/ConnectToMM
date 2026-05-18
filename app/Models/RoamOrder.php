<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoamOrder extends Model
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
    public const OUR_STATUS_ON_HOLD = 9;

    public const ROAM_STATUS_NORMAL = 0;
    public const ROAM_STATUS_UNPAID = 1;
    public const ROAM_STATUS_CANCELLED = 2;
    public const ROAM_STATUS_OBSOLETE = 3;
    public const ROAM_STATUS_PARTIAL_UNSUBSCRIBE = 4;

    public const OUR_STATUS_LABELS = [
        self::OUR_STATUS_ORDER_START => 'Order Start',
        self::OUR_STATUS_PENDING_PAYMENT => 'Pending Payment',
        self::OUR_STATUS_PAID => 'Paid',
        self::OUR_STATUS_ON_HOLD => 'On Hold',
        self::OUR_STATUS_API_PROCESSING => 'Processing',
        self::OUR_STATUS_API_SUCCESS => 'Success',
        self::OUR_STATUS_API_FAILED => 'Failed',
        self::OUR_STATUS_COMPLETED => 'Completed',
        self::OUR_STATUS_CANCELLED => 'Cancelled',
        self::OUR_STATUS_REFUNDED => 'Refunded',
    ];

    public const CUSTOMER_STATUS_LABELS = [
        self::OUR_STATUS_ORDER_START => 'Order Started',
        self::OUR_STATUS_PENDING_PAYMENT => 'Pending Payment',
        self::OUR_STATUS_PAID => 'Paid',
        self::OUR_STATUS_ON_HOLD => 'On Hold',
        self::OUR_STATUS_API_PROCESSING => 'Processing',
        self::OUR_STATUS_API_SUCCESS => 'Success',
        self::OUR_STATUS_API_FAILED => 'Failed',
        self::OUR_STATUS_COMPLETED => 'Completed',
        self::OUR_STATUS_CANCELLED => 'Cancelled',
        self::OUR_STATUS_REFUNDED => 'Refunded',
    ];

    public const ROAM_STATUS_LABELS = [
        self::ROAM_STATUS_NORMAL => 'Normal / Paid',
        self::ROAM_STATUS_UNPAID => 'Unpaid',
        self::ROAM_STATUS_CANCELLED => 'Cancel',
        self::ROAM_STATUS_OBSOLETE => 'Obsolete',
        self::ROAM_STATUS_PARTIAL_UNSUBSCRIBE => 'Partial Unsubscribe',
    ];

    public const OUR_STATUS_TRANSITIONS = [
        self::OUR_STATUS_ORDER_START => [self::OUR_STATUS_PENDING_PAYMENT],
        self::OUR_STATUS_PENDING_PAYMENT => [self::OUR_STATUS_PAID, self::OUR_STATUS_CANCELLED],
        self::OUR_STATUS_PAID => [self::OUR_STATUS_ON_HOLD],
        self::OUR_STATUS_ON_HOLD => [self::OUR_STATUS_API_PROCESSING],
        self::OUR_STATUS_API_PROCESSING => [self::OUR_STATUS_API_SUCCESS, self::OUR_STATUS_API_FAILED],
        self::OUR_STATUS_API_FAILED => [self::OUR_STATUS_API_PROCESSING],
        self::OUR_STATUS_API_SUCCESS => [self::OUR_STATUS_COMPLETED],
        self::OUR_STATUS_COMPLETED => [self::OUR_STATUS_REFUNDED],
        self::OUR_STATUS_CANCELLED => [],
        self::OUR_STATUS_REFUNDED => [],
    ];

    protected $fillable = [
        'customer_id',
        'roam_order_num',
        'outer_order_id',
        'sku_id',
        'price_id',
        'api_code',
        'service_type',
        'order_type',
        'source_iccid',
        'quantity',
        'unit_price',
        'total_price',
        'coupon_id',
        'discount_amount',
        'daypass_days',
        'start_date',
        'end_date',
        'our_status',
        'roam_status',
        'renewal',
        'main_order_num',
        'remark',
        'is_send_email',
        'purchase_date',
        'raw_response',
    ];

    protected $casts = [
        'price_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'integer',
        'daypass_days' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'our_status' => 'integer',
        'roam_status' => 'integer',
        'renewal' => 'boolean',
        'is_send_email' => 'boolean',
        'purchase_date' => 'datetime',
        'raw_response' => 'array',
    ];

    public function getCustomerStatusLabelAttribute(): string
    {
        return self::CUSTOMER_STATUS_LABELS[(int) $this->our_status]
            ?? (string) $this->our_status;
    }

    public function getCustomerStatusProgressAttribute(): array
    {
        return [
            [
                'key' => self::OUR_STATUS_ORDER_START,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_ORDER_START],
            ],
            [
                'key' => self::OUR_STATUS_PENDING_PAYMENT,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_PENDING_PAYMENT],
            ],
            [
                'key' => self::OUR_STATUS_PAID,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_PAID],
            ],
            [
                'key' => self::OUR_STATUS_ON_HOLD,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_ON_HOLD],
            ],
            [
                'key' => self::OUR_STATUS_API_PROCESSING,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_API_PROCESSING],
            ],
            [
                'key' => self::OUR_STATUS_API_SUCCESS,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_API_SUCCESS],
            ],
            [
                'key' => self::OUR_STATUS_COMPLETED,
                'label' => self::CUSTOMER_STATUS_LABELS[self::OUR_STATUS_COMPLETED],
            ],
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // public function coupon(): BelongsTo
    // {
    //     return $this->belongsTo(Coupon::class);
    // }

    public function items(): HasMany
    {
        return $this->hasMany(RoamOrderItem::class);
    }
}
