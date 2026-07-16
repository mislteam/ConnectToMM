<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DirectBankCredential;
use App\Models\PaymentSetting;
use App\Models\UabCredential;
use Illuminate\Http\Request;
use App\Payment\Providers\Uab\Enums\PaymentMethod;

class PaymentSettingController extends Controller
{
    public function index()
    {
        $paymentTypes = PaymentSetting::all();
        return view('admin.payment.index', compact('paymentTypes'));
    }

    public function edit(Request $request)
    {
        $request->validate([
            'payment' => 'required|integer',
            'type' => 'required|string'
        ]);
        $payment = PaymentSetting::findOrFail($request->payment);
        $type = match ((int) $payment->id) {
            1 => 'direct_bank_transfer',
            2 => 'online_payment',
            default => str_replace(' ', '_', strtolower($request->type)),
        };

        return view('admin.payment.edit', compact('payment', 'type'));
    }

    public function updateStatus(Request $request)
    {
        $payment = PaymentSetting::findorFail($request->id);
        $payment->status = $request->status;
        $payment->save();
        return response()->json([
            'success' => true,
            'message' => 'Payment Status Updated Successfully!'
        ]);
    }

    public function updateType(Request $request, PaymentSetting $payment)
    {
        $data = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        $payment->update([
            'type' => $data['type'],
        ]);

        return redirect()
            ->route('admin.payment.edit', [
                'payment' => $payment->id,
                'type' => $payment->type,
            ])
            ->with('success', 'Payment Type Updated Successfully!');
    }

    public function directStore(Request $request)
    {
        $data = $request->validate([
            'payment_setting_id' => 'required|integer|exists:payment_setting,id',
            'bank_name' => 'string|required',
            'account_name' => 'string|required',
            'account_number' => 'string|required',
        ]);

        DirectBankCredential::create($data);
        return redirect()->back()->with('success', 'New Bank Account Added Successfully!');
    }

    public function directUpdate(Request $request)
    {
        $data = $request->validate([
            'credential_id' => 'required|integer|exists:direct_bank_credentials,id',
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
        ]);

        $credential = DirectBankCredential::findOrFail($data['credential_id']);

        $credential->update([
            'bank_name' => $data['bank_name'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
        ]);

        return redirect()->back()->with(
            'success',
            'Bank Account Updated Successfully!'
        );
    }

    public function uabUpdate(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|string|max:64',
            'merchant_id' => 'required|string|max:64',
            'base_url' => 'required|url|max:255',
            'client_id' => 'required|string|max:128',
            'client_secret' => 'required|string|max:255',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'ins_id' => 'required|string|max:128',
            'notify_url' => 'required|url|max:255',
            'success_url' => 'required|url|max:255',
            'cancel_url' => 'required|url|max:255',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'required|string|in:' . implode(',', PaymentMethod::values()),
            'billing_address_line1' => 'required|string|max:120',
            'billing_address_line2' => 'required|string|max:120',
            'billing_city' => 'required|string|max:120',
            'billing_postal_code' => 'required|digits_between:5,16',
            'billing_state' => 'required|string|max:64',
            'billing_country' => 'required|string|size:2',
            'payment_setting_id' => 'required|integer|exists:payment_setting,id',
        ]);

        $paymentSetting = PaymentSetting::findOrFail($data['payment_setting_id']);

        UabCredential::updateOrCreate(
            [
                'payment_setting_id' => $paymentSetting->id,
            ],
            array_merge($data, [
                'payment_methods' => implode(',', $data['payment_methods']),
                'merchant_user_id' => $data['merchant_id'],
                'api_url' => $data['base_url'],
                'is_active' => true,
            ])
        );

        return redirect()->back()->with('success', 'UAB Credentials updated Successfully!');
    }

    public function directDelete(Request $request)
    {
        $account = DirectBankCredential::findOrFail($request->id);
        $account->delete();
        session()->flash("success", "Bank Account Deleted Successfully!");
        return response()->json([
            'success' => true,
        ]);
    }
}
