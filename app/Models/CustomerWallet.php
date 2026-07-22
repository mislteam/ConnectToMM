<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'balance',
        'status'
    ];

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
