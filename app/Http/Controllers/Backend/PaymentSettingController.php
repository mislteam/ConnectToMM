<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DirectBankCredential;
use App\Models\PaymentSetting;
use App\Models\UabCredential;
use Illuminate\Http\Request;
use PhpParser\Node\Scalar\MagicConst\Dir;

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
        $type = str_replace(' ', '_', strtolower($request->type));

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
            'merchant_user_id' => 'required|string',
            'api_url' => 'required|string',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'client_secret' => 'required|string'
        ]);
        $uab_credential = UabCredential::first();
        $uab_credential->update($data);
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
