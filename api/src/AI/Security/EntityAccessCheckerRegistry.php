<?php

declare(strict_types=1);

namespace App\AI\Security;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class EntityAccessCheckerRegistry
{
    /**
     * @param iterable<EntityAccessCheckerInterface> $checkers
     */
    public function __construct(
        #[AutowireIterator('ai.entity_access_checker')]
        private iterable $checkers,
    ) {
    }

    public function isGranted(string $class, object $entity): bool
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($class)) {
                return $checker->isGranted($entity);
            }
        }

        return true;
    }
}
