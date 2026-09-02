<?php

declare(strict_types=1);

namespace App\Tests\AI\Security;

use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\Entity\Sales\SalesForecast;
use PHPUnit\Framework\TestCase;

final class EntityAccessCheckerRegistryTest extends TestCase
{
    public function testReturnsTrueWhenNoCheckerSupports(): void
    {
        $checker = $this->createMock(EntityAccessCheckerInterface::class);
        $checker->method('supports')->willReturn(false);

        $registry = new EntityAccessCheckerRegistry([$checker]);

        self::assertTrue($registry->isGranted(SalesForecast::class, new \stdClass()));
    }

    public function testDelegatesToFirstSupportingChecker(): void
    {
        $other = $this->createMock(EntityAccessCheckerInterface::class);
        $other->method('supports')->willReturn(false);
        $other->expects(self::never())->method('isGranted');

        $matching = $this->createMock(EntityAccessCheckerInterface::class);
        $matching->method('supports')->willReturn(true);
        $matching->expects(self::once())->method('isGranted')->willReturn(false);

        $registry = new EntityAccessCheckerRegistry([$other, $matching]);

        self::assertFalse($registry->isGranted(SalesForecast::class, new \stdClass()));
    }
}
