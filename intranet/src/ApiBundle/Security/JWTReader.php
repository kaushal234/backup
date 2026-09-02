<?php

declare(strict_types=1);

namespace ApiBundle\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTReader
{
    private readonly InMemory $publicKey;

    private readonly Sha256 $signer;

    private readonly Configuration $configuration;
    private readonly KernelInterface $kernel;

    public function __construct(string $publicKey, KernelInterface $kernel)
    {
        $this->signer = new Sha256();
        $this->publicKey = InMemory::file($publicKey);
        $this->configuration = Configuration::forSymmetricSigner($this->signer, $this->publicKey);
        $this->kernel = $kernel;
    }

    public function read(string $jwt): array
    {
        /** @var UnencryptedToken $token */
        $token = $this->configuration->parser()->parse($jwt);
        if (!$this->configuration->validator()->validate($token, new SignedWith($this->signer, $this->publicKey))) {
            throw new \InvalidArgumentException('Invalid token.');
        }

        if ('test' !== $this->kernel->getEnvironment() && $token->isExpired(new \DateTimeImmutable('+30 minutes'))) {
            throw new AuthenticationException('Invalid or expired token.');
        }

        return $token->claims()->all();
    }
}
