<?php

namespace App\Payment\Providers\Uab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HostedPaymentCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => ['sometimes', 'string', 'min:20', 'max:32'],
            'merchant_reference' => ['sometimes', 'string', 'max:255'],
            'invoice_no' => ['required', 'string', 'max:32'],
            'order_no' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', Rule::in(['MMK', 'USD'])],
            'payment_method' => ['required', Rule::in(['uabpay', 'visa_master', 'upi', 'mpu', 'mmqr'])],
            'payment_methods' => ['sometimes', 'nullable', 'string', 'max:128'],
            'bill_to_address_line1' => ['required', 'string', 'max:120'],
            'bill_to_address_line2' => ['required', 'string', 'max:120'],
            'bill_to_address_city' => ['required', 'string', 'max:120'],
            'bill_to_address_postal_code' => ['required', 'digits_between:5,16'],
            'bill_to_address_state' => ['required', 'string', 'max:64'],
            'bill_to_address_country' => ['required', 'string', 'size:2'],
            'bill_to_forename' => ['required', 'string', 'max:120'],
            'bill_to_surname' => ['required', 'string', 'max:120'],
            'bill_to_phone' => ['required', 'regex:/^[0-9]{7,13}$/'],
            'bill_to_email' => ['required', 'email', 'max:64'],
            'expired_in_seconds' => ['sometimes', 'integer', 'min:1', 'max:999'],
            'remark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'user_defined_1' => ['sometimes', 'nullable', 'string', 'max:64'],
            'user_defined_2' => ['sometimes', 'nullable', 'string', 'max:64'],
            'user_defined_3' => ['sometimes', 'nullable', 'string', 'max:64'],
            'user_defined_4' => ['sometimes', 'nullable', 'string', 'max:64'],
            'user_defined_5' => ['sometimes', 'nullable', 'string', 'max:64'],
        ];
    }
}
