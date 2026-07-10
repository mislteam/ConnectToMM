<?php

namespace App\Payment\Providers\Uab\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResponseResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'message' => 'UAB login token retrieved successfully.',
            'data' => [
                'access_token' => $this->resource->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->resource->expiresIn,
                'expired_at' => $this->resource->expiredAt->toIso8601String(),
                'cached' => $this->resource->cached,
            ],
        ];
    }
}
