<?php

namespace App\Payment\Providers\Uab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallbackRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'RequestID' => ['required', 'string'],
            'TransactionReferenceNumber' => ['required', 'string'],
            'TransactionID' => ['sometimes', 'nullable', 'string'],
            'Signature' => ['required', 'string'],
        ];
    }
}
