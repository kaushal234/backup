<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Repository\Sales\SalesForecastRepository;

class SalesForecastQuantityByPastMonthsHandler extends SalesForecastQuantityByPastPeriodsHandler
{
    public function __construct(IriConverterInterface $iriConverter, SalesForecastRepository $salesForecastRepository)
    {
        parent::__construct($iriConverter, $salesForecastRepository);
        $this->dateModifier = 'First day of this month';
        $this->defaultEndDate = new \DateTime('midnight first day of this month');
        $this->periodInterval = new \DateInterval('P1M');
        $this->timeSlotFormat = 'Y-m';
        $this->whereClause = 'DATE_FORMAT(snap.snapshotCreatedAt, \'%Y-%m\') = :timeSlot';
    }

    protected function supports(string $x): bool
    {
        return self::MONTH === $x;
    }
}
