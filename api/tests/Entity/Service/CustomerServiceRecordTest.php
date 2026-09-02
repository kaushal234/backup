<?php

declare(strict_types=1);

namespace App\Tests\Entity\Service;

use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\EquipmentRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use PHPUnit\Framework\TestCase;

class CustomerServiceRecordTest extends TestCase
{
    public function testCustomerServiceRecordImpactErAirport()
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecordAirport = new Airport();
        $equipmentRecordAirport->setName('Airport ER');
        $equipmentRecord->setAirport($equipmentRecordAirport);

        $customerServiceRecordAirport = new Airport();
        $customerServiceRecordAirport->setName('Airport CSR');
        $countryAirportCsr = new Country();
        $countryAirportCsr->setName('Country Airport CSR');
        $customerServiceRecordAirport->setCountry($countryAirportCsr);
        $customerServiceRecord = new CustomerServiceRecord();
        $customerServiceRecord->equipmentRecord = $equipmentRecord;
        $customerServiceRecord->setAirport($customerServiceRecordAirport);

        $this->assertSame(
            $equipmentRecord->getAirport(),
            $customerServiceRecordAirport,
        );
        $this->assertSame(
            $equipmentRecord->getDeliveredCountry(),
            $countryAirportCsr,
        );
    }
}
