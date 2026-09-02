<?php

declare(strict_types=1);

namespace App\Tests\Entity\Sales;

use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastSnapshot;
use PHPUnit\Framework\TestCase;

class SalesForecastSnapshotTest extends TestCase
{
    public function testThatSalesForecastSnapshotHasTheSamePropertiesThanSalesForecast()
    {
        $snapshotReflection = new \ReflectionClass(SalesForecastSnapshot::class);
        $salesForecastReflection = new \ReflectionClass(SalesForecast::class);

        $snapshotProperties = array_map(static fn (\ReflectionProperty $property) => $property->getName(), $snapshotReflection->getProperties());

        $salesForecastProperties = array_map(static fn (\ReflectionProperty $property) => $property->getName(), $salesForecastReflection->getProperties());

        self::assertSame([
            'closedAt',
            'closureNotificationSentAt',
            'comment',
            'synchronized',
            'notificationRestricted',
            'notifyPackage',
            'salesForecastFiles',
            'forecastClosures',
            'salesForecastSnapshots',
            'quote',
            'legacyId',
        ], array_values(array_diff($salesForecastProperties, $snapshotProperties)));
    }
}
