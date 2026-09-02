<?php

declare(strict_types=1);

namespace App\AI\Security\MIS;

use App\AI\Security\EntityAccessCheckerInterface;
use App\Entity\MIS\Project\Project;
use App\Security\Provider\Confidential\Task\MIS\Project\ProjectConfidentialSecurityProvider;

class ProjectAccessChecker implements EntityAccessCheckerInterface
{
    public function __construct(
        private readonly ProjectConfidentialSecurityProvider $securityProvider,
    ) {
    }

    public function supports(string $class): bool
    {
        return Project::class === $class;
    }

    /**
     * @param Project $entity
     */
    public function isGranted(object $entity): bool
    {
        return $this->securityProvider->isProjectGranted($entity);
    }
}
