<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerWallet;
use App\Models\WalletTransaction;

class CustomerWalletController extends Controller
{
    public function index(Customer $customer, Request $request)
    {
        $request->validate([
            'topup_date'       => ['nullable', 'date'],
            'transaction_date' => ['nullable', 'date'],
        ]);

        $customer->load('customerWallet');

        $topupRequests = collect();
        $transactions = collect();

        if ($customer->customerWallet) {
            $wallet = $customer->customerWallet;

            $topupRequests = $wallet
                ->walletTransactions()
                ->where('reference_type', 'topup')
                ->when($request->filled('topup_date'), function ($query) use ($request) {
                    $query->whereDate(
                        'created_at',
                        $request->input('topup_date')
                    );
                })
                ->latest()
                ->get();

            $transactions = $wallet
                ->walletTransactions()
                ->when($request->filled('transaction_date'), function ($query) use ($request) {
                    $query->whereDate(
                        'created_at',
                        $request->input('transaction_date')
                    );
                })
                ->latest()
                ->get();
        }

        $isWalletActive = (bool) optional($customer->customerWallet)->status;

        return view('admin.customer.wallet.index', compact(
            'customer',
            'topupRequests',
            'transactions',
            'isWalletActive'
        ));
    }

    public function show(WalletTransaction $transaction, Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        return view('admin.customer.wallet.show', compact('customer', 'transaction'));
    }

    public function create(Customer $customer)
    {
        return view('admin.customer.wallet.create', compact('customer'));
    }

    public function edit()
    {
        return view('admin.customer.wallet.edit');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_action' => ['required', 'in:topup,return'],
            'balance' => ['exclude_unless:transaction_action,topup', 'required', 'integer', 'min:1000'],
            'return_balance' => ['exclude_unless:transaction_action,return', 'required', 'integer', 'min:1'],
            'customer_id' => ['required', 'integer']
        ]);
        $customer = Customer::findOrFail($data['customer_id']);

        $message = DB::transaction(function () use ($customer, $data) {
            $wallet = CustomerWallet::query()
                ->where('customer_id', $customer->id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = CustomerWallet::create([
                    'customer_id' => $customer->id,
                    'balance' => 0,
                ]);
            }

            $currentBalance = (int) $wallet->balance;

            if ($data['transaction_action'] === 'return') {
                $amount = (int) $data['return_balance'];

                if ($currentBalance < $amount) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'return_balance' => 'Return balance cannot be greater than the current wallet balance.',
                    ]);
                }

                $newBalance = $currentBalance - $amount;

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'type' => 'debit',
                    'reference_type' => 'return',
                    'balance_after' => $newBalance,
                    'transaction_state' => WalletTransaction::STATUS_APPROVED
                ]);

                $wallet->update([
                    'balance' => $newBalance,
                ]);

                return 'Return Balance Successfully!';
            }

            $amount = (int) $data['balance'];
            $newBalance = $currentBalance + $amount;

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'credit',
                'reference_type' => 'topup',
                'balance_after' => $newBalance,
                'transaction_state' => WalletTransaction::STATUS_APPROVED
            ]);

            $wallet->update([
                'balance' => $newBalance,
            ]);

            return 'Top Up Successfully!';
        });

        return back()->with('success', $message);
    }

    public function updateStatus(Request $request, \App\Models\CustomerWallet $wallet)
    {
        $validated = $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $wallet->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet Status Updated Successfully'
        ]);
    }


    public function update(Request $request, WalletTransaction $transaction)
    {
        if ($transaction->transaction_state === 'approved') {
            return back()->with('error', 'This transaction has already been approved.');
        }

        DB::transaction(function () use ($transaction) {

            $wallet = $transaction->customerWallet;

            $newBalance = $wallet->balance + $transaction->amount;

            $transaction->update([
                'transaction_state' => 'approved',
                'balance_after' => $newBalance,
            ]);

            $wallet->update([
                'balance' => $newBalance,
            ]);
        });

        return back()->with('success', 'Top Up approved successfully!');
    }
}
