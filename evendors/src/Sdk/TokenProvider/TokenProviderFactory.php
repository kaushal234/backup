<?php

declare(strict_types=1);

namespace App\Sdk\TokenProvider;

final class TokenProviderFactory
{
    public function __construct(
        private readonly UserTokenProvider $tokenProvider,
    ) {
    }

    public function getProvider(): TokenProviderInterface
    {
        return $this->tokenProvider;
    }
}
