<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    const STATUS_PENDING = "pending";
    const STATUS_APPROVED = "approved";

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'reference_type',
        'balance_after',
        'transaction_state',
        'payment_slip'
    ];

    public function customerWallet()
    {
        return $this->belongsTo(CustomerWallet::class, 'wallet_id');
    }
}
