<?php

declare(strict_types=1);

namespace App\Tests\AI\Security\Support;

use App\AI\Security\Support\ManualAccessChecker;
use App\Entity\Support\Manual;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class ManualAccessCheckerTest extends TestCase
{
    public function testSupportsOnlyManual(): void
    {
        $checker = new ManualAccessChecker($this->createMock(Security::class));

        self::assertTrue($checker->supports(Manual::class));
        self::assertFalse($checker->supports(\stdClass::class));
    }

    public function testIsGrantedDelegatesToVoter(): void
    {
        $entity = $this->createMock(Manual::class);

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('isGranted')
            ->with('MANUAL_ACCESS_VOTER', $entity)
            ->willReturn(true);

        $checker = new ManualAccessChecker($security);

        self::assertTrue($checker->isGranted($entity));
    }
}
