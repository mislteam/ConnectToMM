<?php

namespace App\Payment\Providers\Uab\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionStatusResponseResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'message' => 'UAB transaction status retrieved successfully.',
            'data' => [
                'request_id' => $this->resource->requestId,
                'transaction_id' => $this->resource->transactionId,
                'status' => $this->resource->status->value,
            ],
        ];
    }
}
