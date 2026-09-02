<?php

declare(strict_types=1);

namespace App\Tests\AI\Security\DMS;

use App\AI\Security\DMS\DMSAccessChecker;
use App\Entity\DMS;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class DMSAccessCheckerTest extends TestCase
{
    public function testSupportsOnlyDMS(): void
    {
        $checker = new DMSAccessChecker($this->createMock(Security::class));

        self::assertTrue($checker->supports(DMS::class));
        self::assertFalse($checker->supports(\stdClass::class));
    }

    public function testIsGrantedDelegatesToVoter(): void
    {
        $entity = $this->createMock(DMS::class);

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('isGranted')
            ->with('DMS_PEOPLE_VIEW_VOTER', $entity)
            ->willReturn(true);

        $checker = new DMSAccessChecker($security);

        self::assertTrue($checker->isGranted($entity));
    }
}
