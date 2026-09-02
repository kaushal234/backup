<?php

declare(strict_types=1);

namespace App\AI\Security\DMS;

use App\AI\Security\EntityAccessCheckerInterface;
use App\Entity\DMS;
use Symfony\Bundle\SecurityBundle\Security;

class DMSAccessChecker implements EntityAccessCheckerInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $class): bool
    {
        return DMS::class === $class;
    }

    public function isGranted(object $entity): bool
    {
        return $this->security->isGranted('DMS_PEOPLE_VIEW_VOTER', $entity);
    }
}
