<?php

namespace App\Services\Wallet;

use App\Models\Customer;
use App\Models\CustomerWallet;
use App\Models\WalletTransaction;
use RuntimeException;

class WalletPackagePaymentService
{
    public function debitPackagePurchase(Customer $customer, int $amount): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Package price is invalid.');
        }

        $wallet = CustomerWallet::query()
            ->where('customer_id', $customer->id)
            ->where('status', 1)
            ->lockForUpdate()
            ->first();

        if (!$wallet) {
            throw new RuntimeException('Your wallet is not active. Please top up your wallet before paying with wallet.');
        }

        $currentBalance = (int) $wallet->balance;
        if ($currentBalance < $amount) {
            throw new RuntimeException('Insufficient wallet balance. Please top up your wallet before paying with wallet.');
        }

        $balanceAfter = $currentBalance - $amount;

        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'debit',
            'reference_type' => 'package_purchase',
            'balance_after' => $balanceAfter,
            'transaction_state' => WalletTransaction::STATUS_APPROVED,
            'payment_slip' => null,
        ]);

        $wallet->update([
            'balance' => $balanceAfter,
        ]);

        return $transaction;
    }
}
