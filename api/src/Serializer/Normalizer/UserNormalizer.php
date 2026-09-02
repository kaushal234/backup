<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\User;
use App\Manager\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class UserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    final public const JWT_PAYLOAD = 'jwt_payload';

    public function __construct(
        protected TokenStorageInterface $tokenStorage)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    /**
     * @param User $object
     */
    protected function extractRoles(object $object, array $context): array
    {
        $roles = [];

        if ('sso' === ($context[self::JWT_PAYLOAD]['from'] ?? null)) {
            $roles[] = 'ROLE_PASSWORD_NOT_EXPIRED'; // Used to avoid redirection
            $roles[] = 'ROLE_PASSWORD_NOT_EXPIRING'; // Used to avoid password flash message
        }
        if ($context[self::JWT_PAYLOAD][UserManager::JWT_PROPERTY_ORIGIN] ?? null) {
            $roles[] = 'ROLE_IMPERSONATED';
        }

        if (null === ($token = $this->tokenStorage->getToken()) || $object->getUserIdentifier() !== $token->getUserIdentifier()) {
            return array_unique($roles);
        }

        if (!$object->isPasswordExpired()) {
            $roles[] = 'ROLE_PASSWORD_NOT_EXPIRED';
        }

        return array_unique($roles);
    }
}
