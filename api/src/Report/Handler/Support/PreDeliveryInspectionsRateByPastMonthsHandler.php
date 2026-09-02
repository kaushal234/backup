<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Repository\EquipmentRecordRepository;

class PreDeliveryInspectionsRateByPastMonthsHandler extends PreDeliveryInspectionsRateByPastPeriodsHandler
{
    public function __construct(IriConverterInterface $iriConverter, EquipmentRecordRepository $equipmentRecordRepository)
    {
        parent::__construct($iriConverter, $equipmentRecordRepository);
        $this->dateModifier = 'First day of this month';
        $this->defaultEndDate = new \DateTime('midnight first day of this month');
        $this->periodInterval = new \DateInterval('P1M');
        $this->timeSlotFormat = 'Y-m';
        $this->whereClause = 'DATE_FORMAT(equipmentRecord.greenTagDate, \'%Y-%m\') = :timeSlot';
    }

    protected function supports(string $x): bool
    {
        return self::MONTH === $x;
    }
}
