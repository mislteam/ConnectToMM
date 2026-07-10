<?php

namespace App\Payment\Providers\Uab\Repositories;

use App\Models\UabGatewayToken;
use Carbon\CarbonInterface;

class GatewayTokenRepository
{
    public function __construct(
        private readonly UabGatewayToken $model,
    ) {
    }

    public function latest(): ?UabGatewayToken
    {
        return $this->model->newQuery()
            ->latest('id')
            ->first();
    }

    public function store(string $accessToken, CarbonInterface $expiredAt): UabGatewayToken
    {
        $token = $this->latest();

        if ($token instanceof UabGatewayToken) {
            $token->fill([
                'access_token' => $accessToken,
                'expired_at' => $expiredAt,
            ])->save();

            return $token->refresh();
        }

        return $this->model->newQuery()->create([
            'access_token' => $accessToken,
            'expired_at' => $expiredAt,
        ]);
    }
}
