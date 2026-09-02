<?php

declare(strict_types=1);

namespace App\Tests\Notifier\Support;

use App\Entity\EquipmentRecord;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Notifier\Support\EquipmentRecord\RecipientsFinder;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentRecordNotifierTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testbuildContext()
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setSerialNumber('T118218');

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any(), Argument::any(), ['groups' => ['equipment_shipping_record_line:detail', 'equipment_record_notification', 'odp:view', 'equipment_record:fms_contract', 'equipment_record_detail', 'order_factory', 'order_line', 'customer_list', 'location_public', 'expose_legacy']])->shouldBeCalledOnce()->willReturn([]);
        $recipientsFinderProphecy = $this->prophesize(RecipientsFinder::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->getMock();
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)->disableOriginalConstructor()->getMock();
        $mailerProphecy = $this->prophesize(MailerInterface::class);

        $equipmentRecordNotifier = new EquipmentRecordNotifier($normalizerProphecy->reveal(), $recipientsFinderProphecy->reveal(), $mailerProphecy->reveal(),
            $peopleRepositoryMock, $locationRepositoryMock);

        $reflectionClass = new \ReflectionClass(EquipmentRecordNotifier::class);
        $reflectionMethod = $reflectionClass->getMethod('buildContext');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invokeArgs($equipmentRecordNotifier, [$equipmentRecord]);
    }
}
