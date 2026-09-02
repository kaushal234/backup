<?php

declare(strict_types=1);

namespace App\Security\JWT\PayloadGenerator;

use App\Entity\AuthorizedApplication;
use App\Manager\UserManager;
use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizedApplicationPayloadGenerator implements PayloadGeneratorInterface
{
    public function generate(array &$payload, ?UserInterface $user = null): void
    {
        if (!$user instanceof AuthorizedApplication) {
            return;
        }

        $payload = array_merge(
            $payload,
            [
                'generatedAt' => $user->keyGeneratedOn->format(\DATE_ATOM),
                UserManager::LOGIN_PORTAL => 'external',
                UserManager::JWT_PROPERTY_USERNAME => $user->getUserIdentifier(),
                RegisteredClaims::ISSUED_AT => \DateTimeImmutable::createFromMutable($user->keyGeneratedOn),
                RegisteredClaims::EXPIRATION_TIME => \DateTimeImmutable::createFromMutable($user->keyExpiresOn),
            ]
        );
    }
}
