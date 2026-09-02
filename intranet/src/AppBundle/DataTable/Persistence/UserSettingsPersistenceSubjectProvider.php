<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use ApiBundle\Model\User;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectNotFoundException;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSettingsPersistenceSubjectProvider implements PersistenceSubjectProviderInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(): PersistenceSubjectInterface
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            throw PersistenceSubjectNotFoundException::createForProvider($this);
        }

        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            throw PersistenceSubjectNotFoundException::createForProvider($this);
        }

        // Default use name of module to register in user settings part of the key.
        $moduleName = $request->attributes->get('alvest_module') ?? 'intranet';

        return new UserModulePersistenceSubjectAggregate(
            $user->getId(),
            $moduleName,
        );
    }
}
