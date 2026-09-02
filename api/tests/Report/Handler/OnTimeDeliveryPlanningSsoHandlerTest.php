<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\EquipmentRecord;
use App\Filter\Support\EquipmentRecord\EquipmentRecordOdpFilter;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\Support\OnTimeDeliveryPlanningSsoHandler;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class OnTimeDeliveryPlanningSsoHandlerTest extends TestCase
{
    public function testHandle()
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $connectionMock = $this->createMock(Connection::class);
        $iriConverterMock = $this->createMock(IriConverterInterface::class);

        $handler = new OnTimeDeliveryPlanningSsoHandler($entityManagerMock, $connectionMock, $iriConverterMock);

        $provider = $handler->handle(EquipmentRecord::class, 'salesOrganisation', 'dashboard');

        $this->assertInstanceOf(ReportDataProvider::class, $provider);

        $data = $provider->provideMetadata();

        $expectedXValues = [
            'Backlog' => EquipmentRecordOdpFilter::NOT_SHIPPED,
            'GT Not Shipped (GTNS)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED,
            'YT Not Shipped (YTNS)' => EquipmentRecordOdpFilter::YT_NOT_SHIPPED,
            'GT not shipped with Shipment Authorization Granted, but no Estimated Pick Up Date, Customer responsible for pick up (Incoterms ExW/FCA)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_CUSTOMER_RESPONSIBLE,
            'GT not shipped with Shipment Authorization Granted, but no Estimated Pick Up Date, TLD responsible for pick up (Incoterms NOT ExW/FCA)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_TLD_RESPONSIBLE,
            'GT not shipped with Shipment Authorization NOT granted' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_NOT_GRANTED,
        ];
        $this->assertSame($expectedXValues, $data['xIris']);
    }
}
