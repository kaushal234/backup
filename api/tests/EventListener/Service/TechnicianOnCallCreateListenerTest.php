<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Service\TechnicianOnCall;
use App\EventListener\Service\TechnicianOnCall\TechnicianOnCallCreateListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TechnicianOnCallCreateListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testWhenClassIsNotTechnicianOnCallEventIsNotTrigger(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldNotBeCalled();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            null
        );

        $listener = new TechnicianOnCallCreateListener($containerProphecy->reveal());
        $listener->isTechnicianOnCallCreation($event);
    }

    public function testWhenHTTPMethodIsNotPostEventIsNotTrigger(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->equipmentRecord = new EquipmentRecord();

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_PUT);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($containerProphecy->reveal());
        $listener->isTechnicianOnCallCreation($event);
        self::assertNull($technicianOnCall->equipmentRecord->getAirport());
    }

    public function testWhenTechnicianOnCallAndEquipmentRecordHaveTheSameAirportNothingIsDone(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);

        $airport = new Airport();

        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $equipmentRecordProphecy->getAirport()->shouldBeCalledOnce()->willReturn($airport);
        $equipmentRecordProphecy->setAirport(Argument::any())->shouldNotBeCalled();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->airport = $airport;
        $technicianOnCall->equipmentRecord = $equipmentRecordProphecy->reveal();

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($containerProphecy->reveal());
        $listener->onCreationUpdateEquipmentRecordAirport($event);
    }

    public function testWhenTechnicianOnCallAndEquipmentRecordNotHaveTheSameAirportEquipmentRecordAirportIsUpdated(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);

        $technicianOnCallAirport = new Airport();
        $equipmentRecordAirport = new Airport();

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setAirport($equipmentRecordAirport);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->airport = $technicianOnCallAirport;
        $technicianOnCall->equipmentRecord = $equipmentRecord;

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($containerProphecy->reveal());
        $listener->onCreationUpdateEquipmentRecordAirport($event);

        self::assertSame($technicianOnCallAirport, $equipmentRecord->getAirport());
    }

    public function testWhenTechnicianOnCallAndEquipmentRecordHaveTheSameSSOServiceNothingIsDone(): void
    {
        $location = new Location();

        $containerProphecy = $this->prophesize(ContainerInterface::class);

        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $equipmentRecordProphecy->getSalesOrganisationService()->shouldBeCalledOnce()->willReturn($location);
        $equipmentRecordProphecy->setSalesOrganisationService(Argument::any())->shouldNotBeCalled();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->salesOrganisationService = $location;
        $technicianOnCall->equipmentRecord = $equipmentRecordProphecy->reveal();

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($containerProphecy->reveal());
        $listener->onCreationUpdateEquipmentRecordSalesOrganisationService($event);
    }

    public function testWhenTechnicianOnCallAndEquipmentRecordNotHaveTheSameSSOServiceEquipmentRecordSSOServiceIsUpdated(): void
    {
        $technicianOnCallLocation = new Location();
        $equipmentRecordLocation = new Location();

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setSalesOrganisationService($equipmentRecordLocation);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->salesOrganisationService = $technicianOnCallLocation;
        $technicianOnCall->equipmentRecord = $equipmentRecord;

        $containerProphecy = $this->prophesize(ContainerInterface::class);

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($containerProphecy->reveal());
        $listener->onCreationUpdateEquipmentRecordSalesOrganisationService($event);

        self::assertSame($technicianOnCallLocation, $equipmentRecord->getSalesOrganisationService());
    }

    public function testWhenSerialNumberIsNullNothingIsDone(): void
    {
        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $equipmentRecordProphecy->getCustomerSerialNumber()->shouldNotBeCalled();
        $equipmentRecordProphecy->setCustomerSerialNumber(Argument::any())->shouldNotBeCalled();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->serialNumber = null;
        $technicianOnCall->equipmentRecord = $equipmentRecordProphecy->reveal();

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($this->prophesize(ContainerInterface::class)->reveal());
        $listener->onCreationUpdateEquipmentRecordCustomerSerialNumber($event);
    }

    public function testWhenSerialNumberMatchesCustomerSerialNumberNothingIsDone(): void
    {
        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $equipmentRecordProphecy->getCustomerSerialNumber()->shouldBeCalledOnce()->willReturn('SN-123');
        $equipmentRecordProphecy->setCustomerSerialNumber(Argument::any())->shouldNotBeCalled();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->serialNumber = 'SN-123';
        $technicianOnCall->equipmentRecord = $equipmentRecordProphecy->reveal();

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($this->prophesize(ContainerInterface::class)->reveal());
        $listener->onCreationUpdateEquipmentRecordCustomerSerialNumber($event);
    }

    public function testWhenSerialNumberDiffersFromCustomerSerialNumberEquipmentRecordIsUpdated(): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setCustomerSerialNumber('OLD-SN');

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->serialNumber = 'NEW-SN';
        $technicianOnCall->equipmentRecord = $equipmentRecord;

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->getMethod()->shouldBeCalledOnce()->willReturn(Request::METHOD_POST);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $technicianOnCall
        );

        $listener = new TechnicianOnCallCreateListener($this->prophesize(ContainerInterface::class)->reveal());
        $listener->onCreationUpdateEquipmentRecordCustomerSerialNumber($event);

        self::assertSame('NEW-SN', $equipmentRecord->getCustomerSerialNumber());
    }
}
