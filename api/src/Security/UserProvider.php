<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Directory\People;
use App\Entity\User;
use App\Manager\UserManager;
use App\Repository\AclRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements PayloadAwareUserProviderInterface
{
    public function __construct(
        private readonly RequestStack $request,
        private readonly UserManager $userManager,
        private readonly AclRepository $aclRepository,
    ) {
    }

    public function loadUserByUsername($username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier($identifier): UserInterface
    {
        $portal = $this->request->getCurrentRequest()->get(UserManager::LOGIN_PORTAL);
        $loader = $this->userManager->getUserLoader($portal);

        if (null === $loader || null === ($user = $loader($identifier))) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
        }

        if (UserManager::LINK_PORTAL === $portal && $user instanceof People && !$this->aclRepository->userHasRoles($user, ['LINK_ENG', 'LINK_PROD', 'LINK_SSO_COMMISSIONING', 'LINK_SSO_AST'])) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return UserInterface::class === $class;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifierAndPayload(string $identifier, array $payload): UserInterface
    {
        $loader = $this->userManager->getUserLoader($payload[UserManager::LOGIN_PORTAL] ?? null);

        if (null === $loader || null === ($user = $loader($identifier))) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }
}
