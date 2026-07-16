<?php

namespace App\Models;

use App\Payment\Providers\Uab\Enums\Currency;
use App\Payment\Providers\Uab\Enums\PaymentMethod;
use App\Payment\Providers\Uab\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UabPaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'uab_payment_transactions';

    protected $fillable = [
        'request_id',
        'transaction_id',
        'merchant_reference',
        'invoice_no',
        'order_no',
        'amount',
        'currency',
        'payment_method',
        'selected_payment_method',
        'selected_payment_type',
        'selected_card_type',
        'status',
        'provider_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'currency' => Currency::class,
        'payment_method' => PaymentMethod::class,
        'status' => TransactionStatus::class,
        'provider_response' => 'array',
    ];

    public function paymentApiLogs(): HasMany
    {
        return $this->hasMany(UabPaymentApiLog::class);
    }

    public function callbackLogs(): HasMany
    {
        return $this->hasMany(UabCallbackLog::class);
    }
}
