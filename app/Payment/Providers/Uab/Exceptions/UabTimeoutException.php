<?php

namespace App\Payment\Providers\Uab\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UabTimeoutException extends Exception
{
    public function __construct(string $message = 'UAB login request timed out.')
    {
        parent::__construct($message, 504);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 504);
    }
}
