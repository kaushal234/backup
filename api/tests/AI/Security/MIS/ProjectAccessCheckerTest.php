<?php

declare(strict_types=1);

namespace App\Tests\AI\Security\MIS;

use App\AI\Security\MIS\ProjectAccessChecker;
use App\Entity\MIS\Project\Project;
use App\Security\Provider\Confidential\Task\MIS\Project\ProjectConfidentialSecurityProvider;
use PHPUnit\Framework\TestCase;

final class ProjectAccessCheckerTest extends TestCase
{
    public function testSupportsOnlyProject(): void
    {
        $checker = new ProjectAccessChecker($this->createMock(ProjectConfidentialSecurityProvider::class));

        self::assertTrue($checker->supports(Project::class));
        self::assertFalse($checker->supports(\stdClass::class));
    }

    public function testIsGrantedDelegatesToSecurityProvider(): void
    {
        $entity = $this->createMock(Project::class);

        $provider = $this->createMock(ProjectConfidentialSecurityProvider::class);
        $provider->expects(self::once())
            ->method('isProjectGranted')
            ->with($entity)
            ->willReturn(false);

        $checker = new ProjectAccessChecker($provider);

        self::assertFalse($checker->isGranted($entity));
    }
}
