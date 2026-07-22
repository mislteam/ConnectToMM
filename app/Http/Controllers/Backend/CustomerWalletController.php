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
            'balance' => 'required|integer|min:1000',
            'customer_id' => 'required|integer'
        ]);
        $customer = Customer::findOrFail($data['customer_id']);

        DB::transaction(function () use ($customer, $data) {
            $wallet = CustomerWallet::firstOrCreate(
                ['customer_id' => $customer->id],
                [
                    'balance' => 0
                ]
            );

            $newBalance = $wallet->balance + $data['balance'];

            $wallet->update([
                'balance' => $newBalance,
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $data['balance'],
                'type' => 'credit',
                'reference_type' => 'topup',
                'balance_after' => $newBalance,
                'transaction_state' => 'approved'
            ]);
        });

        return back()->with('success', 'Top Up Successfully!');
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
