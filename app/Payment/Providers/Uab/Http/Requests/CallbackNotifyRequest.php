<?php

namespace App\Payment\Providers\Uab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallbackNotifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'MerchantUserID' => ['required', 'string'],
            'Channel' => ['required', 'string'],
            'RequestID' => ['required', 'string'],
            'PaymentMethod' => ['required', 'string'],
            'PaymentType' => ['sometimes', 'nullable', 'string'],
            'CardType' => ['sometimes', 'nullable', 'string'],
            'Amount' => ['required'],
            'TransactionFee' => ['sometimes'],
            'Currency' => ['required', 'string', 'size:3'],
            'InvoiceNo' => ['required', 'string'],
            'TransactionReferenceNumber' => ['required', 'string'],
            'TransactionID' => ['sometimes', 'nullable', 'string'],
            'UserDefined1' => ['sometimes', 'nullable', 'string'],
            'UserDefined2' => ['sometimes', 'nullable', 'string'],
            'UserDefined3' => ['sometimes', 'nullable', 'string'],
            'UserDefined4' => ['sometimes', 'nullable', 'string'],
            'UserDefined5' => ['sometimes', 'nullable', 'string'],
            'RespCode' => ['required'],
            'RespDescription' => ['required', 'string'],
        ];
    }
}
