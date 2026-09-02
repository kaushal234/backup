<?php

declare(strict_types=1);

namespace App\Test\Security\User;

use App\Security\User\Address;
use App\Security\User\User;
use Psl\Str;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    /**
     * @var array<string, User>
     */
    private array $users;

    public function __construct()
    {
        $address = new Address('foo', 'bar', 'baz', 'qux', 'TN', '8011');

        $this->users = [
            'azjezz' => new User(1, 'Saif Eddin', 'Gmati', 'azjezz', 'password', $address),
            'phillipe' => new User(1, 'Phillipe', 'Carle', 'phillipe', 'password', $address),
            'dummy' => new User(1, 'Dummy', 'Tester', 'dummy', 'password', $address),
        ];
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(Str\format('Invalid user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function loadUserByIdentifier(string $identifier): User
    {
        $user = $this->users[$identifier] ?? null;
        if (null === $user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    public function getUser(string $identifier = 'azjezz'): User
    {
        return $this->loadUserByIdentifier($identifier);
    }
}
