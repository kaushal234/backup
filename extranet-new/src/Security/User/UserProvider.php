<?php

declare(strict_types=1);

namespace App\Security\User;

use App\CQRS\Query\ExtranetUserAcl\FindAllExtranetUserAclsQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Http\Client;
use App\Sdk\Resource\ExtranetUserAcl;
use Doctrine\DBAL\Exception as DoctrineException;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly QueryBusInterface $queryBus,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @param non-empty-string $identifier
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function loadUserByIdentifier(string $identifier): User
    {
        $response = $this->client->request('GET', '/me', ['auth_bearer' => $identifier, 'query' => ['normalizationGroups' => ['extranet_user_acls', 'user_profile_language']]]);
        if ($response->getStatusCode() > 299) {
            throw new AuthenticationServiceException();
        }

        $user = $response->toArray();

        if (empty($user['extranetUserAcls'])) {
            throw new CustomUserMessageAccountStatusException('Your account is activated but not set up correctly, please ask your Alvest representative.');
        }

        /** @var AccessibleCollectionInterface<ExtranetUserAcl> $extranetUserAcls */
        $extranetUserAcls = $this->queryBus->dispatch(new FindAllExtranetUserAclsQuery(['auth_bearer' => $identifier]));

        foreach ($extranetUserAcls as $extranetUserAcl) {
            if (null !== $this->requestStack->getSession()->get('customerRelationshipTeam')) {
                continue;
            }

            $this->requestStack->getSession()->set('customerRelationshipTeam', $extranetUserAcl->customerRelationshipTeam);
            $this->requestStack->getSession()->set('customer', $extranetUserAcl->customerRelationshipTeam->customer);
        }

        return new User(
            id: $user['id'],
            firstname: $user['firstname'],
            lastname: $user['lastname'],
            identifier: $user['email'],
            token: $identifier,
            passwordExpirationDate: $user['passwordExpirationDate'],
            acls: $extranetUserAcls->toArray(),
            language: $user['extranetUserProfile']['language'],
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws DoctrineException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Invalid user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->token);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function updatePassword(PasswordAuthenticatedUserInterface $user, string $password, string $token): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Invalid user class "%s".', $user::class));
        }

        $this->client->request(Request::METHOD_PUT, \sprintf('/sales/extranet_users/%d', $user->id), [
            'auth_bearer' => $user->token,
            'json' => [
                'clearPassword' => $password,
                'token' => $token,
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordConfirmationEmail(PasswordAuthenticatedUserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Invalid user class "%s".', $user::class));
        }

        $this->client->request(Request::METHOD_POST, \sprintf('/sales/extranet_users/%s/update_password', $user->id), ['auth_bearer' => $user->token]);
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
