<?php

declare(strict_types=1);

namespace App\Security\User;

use App\Sdk\Http\ClientInterface;
use Psl\Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use function sprintf;

final class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @param non-empty-string $identifier
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): User
    {
        $response = $this->client->request('GET', '/me', ['auth_bearer' => $identifier]);
        if ($response->getStatusCode() > 299) {
            throw new AuthenticationServiceException();
        }

        $user = $response->toArray();

        return new User(
            id: $user['id'],
            firstname: $user['firstname'],
            lastname: $user['lastname'],
            identifier: $user['email'],
            token: $identifier,
            address: new Address(
                $user['address']['street1'] ?? '',
                $user['address']['street2'] ?? '',
                $user['address']['city'] ?? '',
                $user['address']['state'] ?? '',
                $user['address']['country'] ?? '',
                $user['address']['postalCode'] ?? '',
            ),
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws UnsupportedUserException if the user class is not supported
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(Str\format('Invalid user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->token);
    }

    /**
     * {@inheritDoc}
     *
     * @throws UnsupportedUserException if the user class is not supported
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $password): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(Str\format('Invalid user class "%s".', $user::class));
        }

        $this->client->request(Request::METHOD_PUT, sprintf('/purchasing/vendor_users/%d', $user->id), [
            'auth_bearer' => $user->token,
            'json' => [
                'clearPassword' => $password,
            ],
        ]);
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
