<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Repository\Sales\SalesForecastRepository;

class SalesForecastQuantityByPastWeeksHandler extends SalesForecastQuantityByPastPeriodsHandler
{
    public function __construct(IriConverterInterface $iriConverter, SalesForecastRepository $salesForecastRepository)
    {
        parent::__construct($iriConverter, $salesForecastRepository);
        $this->dateModifier = 'Monday this week';
        $this->defaultEndDate = new \DateTime('last monday');
        $this->periodInterval = new \DateInterval('P1W');
        $this->timeSlotFormat = 'Y-m-d';
        $this->whereClause = 'snap.snapshotCreatedAt = :timeSlot';
    }

    protected function supports(string $x): bool
    {
        return self::WEEK === $x;
    }
}
