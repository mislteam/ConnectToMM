<?php

namespace App\Payment\Providers\Uab\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UabAuthenticationException extends Exception
{
    public function __construct(
        string $message = 'UAB authentication failed.',
        private readonly int $status = 401,
    ) {
        parent::__construct($message, $status);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->status);
    }
}
