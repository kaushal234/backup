<?php

declare(strict_types=1);

namespace App\Tests\AI\Security\Sales;

use App\AI\Security\Sales\SalesForecastAccessChecker;
use App\Entity\Sales\SalesForecast;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class SalesForecastAccessCheckerTest extends TestCase
{
    public function testSupportsOnlySalesForecast(): void
    {
        $checker = new SalesForecastAccessChecker($this->createMock(Security::class));

        self::assertTrue($checker->supports(SalesForecast::class));
        self::assertFalse($checker->supports(\stdClass::class));
    }

    public function testIsGrantedDelegatesToVoter(): void
    {
        $entity = $this->createMock(SalesForecast::class);

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('isGranted')
            ->with('SALES_FORECAST_ACCESS_VOTER', $entity)
            ->willReturn(true);

        $checker = new SalesForecastAccessChecker($security);

        self::assertTrue($checker->isGranted($entity));
    }
}
