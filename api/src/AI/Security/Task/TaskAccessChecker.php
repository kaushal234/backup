<?php

declare(strict_types=1);

namespace App\AI\Security\Task;

use App\AI\Security\EntityAccessCheckerInterface;
use App\Entity\Task\Task;
use App\Security\Provider\Confidential\Task\ConfidentialSecurityProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class TaskAccessChecker implements EntityAccessCheckerInterface
{
    /**
     * @param iterable<ConfidentialSecurityProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('task.security.provider')]
        private readonly iterable $providers,
        private readonly Security $security,
    ) {
    }

    public function supports(string $class): bool
    {
        return Task::class === $class;
    }

    /**
     * @param Task $entity
     */
    public function isGranted(object $entity): bool
    {
        if (!$entity->isConfidential()) {
            return true;
        }

        $user = $this->security->getUser();

        if ($entity->assignee === $user || $entity->createdBy === $user) {
            return true;
        }

        foreach ($this->providers as $provider) {
            if ($provider->isGranted($entity)) {
                return true;
            }
        }

        return false;
    }
}
