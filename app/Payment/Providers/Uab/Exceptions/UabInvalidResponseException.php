<?php

namespace App\Payment\Providers\Uab\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UabInvalidResponseException extends Exception
{
    public function __construct(string $message = 'UAB returned an invalid response.')
    {
        parent::__construct($message, 502);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 502);
    }
}
