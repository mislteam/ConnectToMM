<?php

namespace App\Payment\Providers\Uab\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Payment\Providers\Uab\Contracts\AuthenticationInterface;
use App\Payment\Providers\Uab\DTO\LoginRequestData;
use App\Payment\Providers\Uab\Http\Requests\AuthenticationLoginRequest;
use App\Payment\Providers\Uab\Http\Resources\LoginResponseResource;
use App\Payment\Providers\Uab\Services\UabCredentialService;

class AuthenticationController extends Controller
{
    public function __construct(
        private readonly AuthenticationInterface $authenticationService,
        private readonly UabCredentialService $uabCredentialService,
    ) {}

    public function login(AuthenticationLoginRequest $request): LoginResponseResource
    {
        $credentials = $this->uabCredentialService->getActiveCredential();

        return new LoginResponseResource(
            $this->authenticationService->login(
                new LoginRequestData(
                    clientId: $credentials->clientId,
                    clientSecret: $credentials->clientSecret,
                    insId: $credentials->insId,
                    baseUrl: $credentials->baseUrl,
                    forceRefresh: $request->boolean('force_refresh'),
                    version: $credentials->version,
                    timeoutSeconds: $credentials->timeoutSeconds,
                    tokenBufferSeconds: $credentials->tokenBufferSeconds,
                )
            )
        );
    }
}
