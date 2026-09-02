<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastSnapshot;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class SalesForecastSnapshotFactory
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function createSnapshot(SalesForecast $salesForecast): SalesForecastSnapshot
    {
        $snapshot = new SalesForecastSnapshot();
        $snapshot->setOriginalSalesForecast($salesForecast);

        foreach ((new \ReflectionClass(SalesForecastSnapshot::class))->getProperties() as $property) {
            if (\in_array($property->getName(), ['id', 'snapshotCreatedAt', 'originalSalesForecast'], true)) {
                continue;
            }

            $value = $this->propertyAccessor->getValue($salesForecast, $property->getName());

            $this->propertyAccessor->setValue($snapshot, $property->getName(), $value);
        }

        return $snapshot;
    }
}
