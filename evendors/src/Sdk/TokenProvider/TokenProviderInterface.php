<?php

declare(strict_types=1);

namespace App\Sdk\TokenProvider;

interface TokenProviderInterface
{
    /**
     * Retrieve JWT token.
     */
    public function getToken(): string;
}
