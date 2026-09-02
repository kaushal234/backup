<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\EquipmentRecord;
use PHPUnit\Framework\TestCase;

class EquipmentRecordTest extends TestCase
{
    public function testSetEquipmentRecordAirportImpactCountry()
    {
        $equipmentRecord = new EquipmentRecord();
        $airport = new Airport();
        $airport->setName('CDG');
        $airportCountry = new Country();
        $airportCountry->setName('Paris');
        $airport->setCountry($airportCountry);
        $equipmentRecord->setAirport($airport);

        $newAirport = new Airport();
        $newAirport->setName('TUF');
        $newAirportCountry = new Country();
        $newAirportCountry->setName('Tours');
        $newAirport->setCountry($newAirportCountry);
        $equipmentRecord->setAirport($newAirport);

        $this->assertSame(
            $equipmentRecord->getDeliveredCountry(),
            $newAirportCountry,
        );
    }

    public function testWarrantyStatusIsNotShippedWhenDateShippedIsNull(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2099-01-01'));
        $equipmentRecord->setWarrantyConditions('');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_NOT_SHIPPED, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusIsEffectiveWhenUnderTwoThousandHoursAndNoConditions(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-1 month'));
        $equipmentRecord->setHourMeter(500);
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2099-01-01'));
        $equipmentRecord->setWarrantyConditions('');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_EFFECTIVE, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusIsMaybeExpiredWhenHoursAreBetweenTwoAndThreeThousand(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-1 month'));
        $equipmentRecord->setHourMeter(2500);
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2099-01-01'));
        $equipmentRecord->setWarrantyConditions('');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_MAYBE_EXPIRED, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusIsMaybeExpiredWhenUnderTwoThousandHoursWithSpecialConditions(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-1 month'));
        $equipmentRecord->setHourMeter(500);
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2099-01-01'));
        $equipmentRecord->setWarrantyConditions('Some special condition');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_MAYBE_EXPIRED, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusIsExpiredWhenWarrantyEndDateIsInThePast(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-5 years'));
        $equipmentRecord->setHourMeter(500);
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2000-01-01'));
        $equipmentRecord->setWarrantyConditions('');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_EXPIRED, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusIsExpiredWhenHoursExceedThreeThousand(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-1 month'));
        $equipmentRecord->setHourMeter(4000);
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2099-01-01'));
        $equipmentRecord->setWarrantyConditions('');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_EXPIRED, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusIsExpiredWhenWarrantyEndDateIsNull(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-1 month'));
        $equipmentRecord->setHourMeter(500);
        $equipmentRecord->setWarrantyConditions('');

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_EXPIRED, $equipmentRecord->getWarrantyStatus());
    }

    public function testWarrantyStatusTreatsNullWarrantyConditionsAsNoConditions(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped(new \DateTime('-1 month'));
        $equipmentRecord->setHourMeter(500);
        $equipmentRecord->setWarrantyEndDate(new \DateTime('2099-01-01'));

        $this->assertSame(EquipmentRecord::WARRANTY_STATUS_EFFECTIVE, $equipmentRecord->getWarrantyStatus());
    }
}
