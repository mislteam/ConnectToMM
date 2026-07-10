<?php

namespace App\Payment\Providers\Uab\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostedPaymentResponseResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'message' => 'UAB hosted checkout created successfully.',
            'data' => [
                'request_id' => $this->resource->requestId,
                'transaction_id' => $this->resource->transactionId,
                'payment_url' => $this->resource->paymentUrl,
                'status' => $this->resource->status->value,
            ],
        ];
    }
}
