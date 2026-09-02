<?php

declare(strict_types=1);

namespace App\AI\Security\Support;

use App\AI\Security\EntityAccessCheckerInterface;
use App\Entity\Support\Manual;
use Symfony\Bundle\SecurityBundle\Security;

class ManualAccessChecker implements EntityAccessCheckerInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $class): bool
    {
        return Manual::class === $class;
    }

    public function isGranted(object $entity): bool
    {
        return $this->security->isGranted('MANUAL_ACCESS_VOTER', $entity);
    }
}
