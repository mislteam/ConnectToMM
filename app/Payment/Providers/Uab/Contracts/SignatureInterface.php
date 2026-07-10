<?php

namespace App\Payment\Providers\Uab\Contracts;

interface SignatureInterface
{
    public function generate(array $payload, array $context = []): string;

    public function verify(array $payload, string $signature, array $context = []): bool;
}
