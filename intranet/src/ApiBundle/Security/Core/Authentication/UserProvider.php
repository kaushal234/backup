<?php

declare(strict_types=1);

namespace ApiBundle\Security\Core\Authentication;

use ApiBundle\Model\BusinessUnit;
use ApiBundle\Model\User;
use ApiBundle\Security\JWTReader;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly JWTReader $reader,
    ) {
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        throw new \Exception('Not used');
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $payload = $this->reader->read($identifier);
        } catch (\InvalidArgumentException $exception) {
            throw new UserNotFoundException($exception->getMessage());
        }

        if (!($payload['@id'] ?? null)) {
            throw new UserNotFoundException('User not found.');
        }

        if ($payload['iat'] > new \DateTime('2 minutes ago')) {
            $payload['roles'][] = 'ROLE_AUTHENTICATED_FRESH';
        } else {
            $payload['roles'][] = 'ROLE_NOT_AUTHENTICATED_FRESH';
        }

        $businessUnit = isset($payload['businessUnit']) ? (array) $payload['businessUnit'] : null;

        return new User(
            iriId: $payload['@id'],
            iriType: $payload['@type'],
            username: $payload['username'],
            firstname: $payload['firstname'],
            lastname: $payload['lastname'],
            disabled: $payload['disabled'],
            hidden: $payload['hidden'],
            expirationDate: $payload['passwordExpirationDate'],
            photo: $payload['photo'] ?? [],
            roles: $payload['roles'] ?? [],
            acls: $payload['acls'] ?? [],
            businessUnit: null === $businessUnit ? null : new BusinessUnit($businessUnit['@id'], $businessUnit['@type'], $businessUnit['id'], $businessUnit['name']),
            token: $identifier,
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getToken());
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
