<?php

declare(strict_types=1);

namespace App\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

final readonly class TokenGenerator
{
    public function __construct(
        private string $mercureJwtSecret,
    ) {
    }

    public function generateForTopic(string $topic): string
    {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->mercureJwtSecret));

        $now = new \DateTimeImmutable();

        $token = $config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify('+30 minutes'))
            ->withClaim('mercure', [
                'subscribe' => [$topic],
            ])
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }
}
