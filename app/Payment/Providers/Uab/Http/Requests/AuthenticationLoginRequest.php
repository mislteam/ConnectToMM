<?php

namespace App\Payment\Providers\Uab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticationLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'force_refresh' => ['sometimes', 'boolean'],
        ];
    }
}
