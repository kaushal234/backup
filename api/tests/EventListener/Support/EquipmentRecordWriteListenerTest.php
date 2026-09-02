<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Support;

use App\Entity\EquipmentRecord;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use App\EventListener\Support\EquipmentRecordWriteListener;
use App\Manager\EquipmentRecordManager;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Repository\Support\EquipmentSerialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EquipmentRecordWriteListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testNotEquipmentRecord()
    {
        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            0,
            null
        );

        $listener = new EquipmentRecordWriteListener(static::getContainer());
        $result = $listener->preWrite($event);

        $this->assertNull($result);
    }

    public function testNotPutRequest()
    {
        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_PUT)->willReturn(false);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            new EquipmentRecord()
        );

        $listener = new EquipmentRecordWriteListener(static::getContainer());
        $result = $listener->preWrite($event);

        $this->assertNull($result);
    }

    public function testResetDeletedSerialTypeOfManual()
    {
        $manualComponent = new Component();
        $manualComponent->name = 'MANUAL';
        $reflectionClass = new \ReflectionClass($manualComponent);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($manualComponent, '44');

        $manualSerialTryToDelete = new EquipmentSerial();
        $manualSerialTryToDelete->component = $manualComponent;
        $manualSerialTryToDelete->serial = "Si ju vas bien, c'est juvamine";
        $reflectionClass = new \ReflectionClass($manualSerialTryToDelete);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($manualSerialTryToDelete, 824974);

        $manualSerialTryToCreate = new EquipmentSerial();
        $manualSerialTryToCreate->component = $manualComponent;
        $manualSerialTryToCreate->serial = 'Bye, Bye !';

        $otherComponent = new Component();
        $otherComponent->name = 'OTHER';
        $reflectionClass = new \ReflectionClass($otherComponent);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($otherComponent, 5);

        $manualSerialTryToModify = new EquipmentSerial();
        $manualSerialTryToModify->component = $otherComponent;
        $manualSerialTryToModify->serial = "J'essaye de modifier le serial";
        $reflectionClass = new \ReflectionClass($manualSerialTryToModify);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($manualSerialTryToModify, 824975);

        $otherSerial = new EquipmentSerial();
        $otherSerial->component = $otherComponent;
        $otherSerial->serial = 'Kiri kiri kiriiiiii';
        $reflectionClass = new \ReflectionClass($otherSerial);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($otherSerial, 862257);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->addSerial($otherSerial);
        $equipmentRecord->addSerial($manualSerialTryToCreate);
        $equipmentRecord->addSerial($manualSerialTryToModify);

        $originalManualSerials = [
            [
                'model' => 'TMX-50-6',
                'serial' => "Si ju vas bien, c'est juvamine",
                'brand' => 'TLD',
                'id' => '824974',
                'componentId' => '44',
                'componentName' => 'MANUAL',
            ],
            [
                'model' => 'TMX-50-6',
                'serial' => 'ChausséE aux moiiines',
                'brand' => 'TLD',
                'id' => '824975',
                'componentId' => '44',
                'componentName' => 'MANUAL',
            ],
        ];

        $serialRepositoryMock = $this->getMockBuilder(EquipmentSerialRepository::class)->disableOriginalConstructor()->onlyMethods(['findByComponentNameAndEquipmentRecord', 'find'])->getMock();
        $serialRepositoryMock
            ->expects($this->once())
            ->method('findByComponentNameAndEquipmentRecord')
            ->with($this->callback(static fn ($equipment) => $equipment instanceof EquipmentRecord), 'MANUAL')
            ->willreturn($originalManualSerials)
        ;

        $serialRepositoryMock->expects($this->once())->method('find')->with(824974)->willReturn($manualSerialTryToDelete);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(EquipmentSerial::class)->willReturn($serialRepositoryMock);
        $entityManagerProphecy->contains(Argument::type(EquipmentSerial::class))->shouldBeCalledTimes(3)->willReturn(true, false, true);
        $entityManagerProphecy->refresh(Argument::type(EquipmentSerial::class))->shouldBeCalledTimes(1)->will(static function ($promise) use ($manualComponent) {
            $promise[0]->serial = 'ChausséE aux moiiines';
            $promise[0]->component = $manualComponent;
        });

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(EntityManagerInterface::class)->willReturn($entityManagerProphecy->reveal());

        $listener = new EquipmentRecordWriteListener($containerProphecy->reveal());

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_PUT)->willReturn(true);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $requestProphecy->reveal(),
            0,
            $equipmentRecord
        );

        $listener->preWrite($event);
        $this->assertCount(3, $equipmentRecord->getSerials());

        $this->assertSame('Kiri kiri kiriiiiii', $equipmentRecord->getSerials()->offsetGet(0)->serial);
        $this->assertNull($equipmentRecord->getSerials()->offsetGet(1));
        $this->assertSame('Si ju vas bien, c\'est juvamine', $equipmentRecord->getSerials()->offsetGet(3)->serial);
    }

    /**
     * @dataProvider dataProviderPostWriteOdpReturnNull
     */
    public function testNotNullPreviousData($previousData, $requestMethod, $controllerResult)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Security::class)->shouldNotBeCalled();

        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn($previousData)->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod($requestMethod);

        $request->attributes = $bagProphecy->reveal();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $controllerResult
        );

        (new EquipmentRecordWriteListener($serviceLocatorProphecy->reveal()))->equipmentRecordOdpPostWrite($event);
    }

    public function dataProviderPostWriteOdpReturnNull()
    {
        yield 'null controller result' => [new EquipmentRecord(), Request::METHOD_PUT, null];
        yield 'method post' => [new EquipmentRecord(), Request::METHOD_POST, new EquipmentRecord()];
        yield 'null previous data' => [null, Request::METHOD_PUT, new EquipmentRecord()];
    }

    public function testNotSendAlertEstimatedGreenTagDateGapNotification()
    {
        $userProphecy = $this->prophesize(UserInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $equipmentRecordNotifierProphecy = $this->prophesize(EquipmentRecordNotifier::class);
        $equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
        $securityProphecy->getUser()->willReturn($userProphecy->reveal());
        $newEquipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $newEquipmentRecord = $newEquipmentRecordProphecy->reveal();

        $previousEquipment = new EquipmentRecord();
        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn($previousEquipment)->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes = $bagProphecy->reveal();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Security::class)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(EquipmentRecordNotifier::class)->willReturn($equipmentRecordNotifierProphecy->reveal());
        $serviceLocatorProphecy->get(EquipmentRecordManager::class)->willReturn($equipmentRecordManagerProphecy->reveal());

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $newEquipmentRecord
        );

        $equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert($newEquipmentRecord, $previousEquipment->getEstimatedGreenTagDate())->shouldBeCalledOnce()->willReturn(false);
        $equipmentRecordNotifierProphecy->sendAlertEstimatedGreenTagDateGap(Argument::cetera())->shouldNotBeCalled();

        $listener = new EquipmentRecordWriteListener($serviceLocatorProphecy->reveal());
        $listener->equipmentRecordOdpPostWrite($event);
    }

    public function testSendAlertEstimatedGreenTagDateGapNotification()
    {
        $userProphecy = $this->prophesize(UserInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $equipmentRecordNotifierProphecy = $this->prophesize(EquipmentRecordNotifier::class);
        $equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
        $securityProphecy->getUser()->willReturn($userProphecy->reveal());

        $newEquipmentRecord = new EquipmentRecord();

        $previousEquipment = new EquipmentRecord();
        $previousEquipment->setEstimatedGreenTagDate(new \DateTime());
        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn($previousEquipment)->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes = $bagProphecy->reveal();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Security::class)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(EquipmentRecordNotifier::class)->willReturn($equipmentRecordNotifierProphecy->reveal());

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $newEquipmentRecord
        );

        $equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert($newEquipmentRecord, $previousEquipment->getEstimatedGreenTagDate())->shouldBeCalledOnce()->willReturn(true);
        $equipmentRecordNotifierProphecy->sendAlertEstimatedGreenTagDateGap($newEquipmentRecord, $previousEquipment->getEstimatedGreenTagDate()->format('Y-m-d'))->shouldBeCalledOnce();

        $serviceLocatorProphecy->get(EquipmentRecordManager::class)->willReturn($equipmentRecordManagerProphecy->reveal());

        $listener = new EquipmentRecordWriteListener($serviceLocatorProphecy->reveal());
        $listener->equipmentRecordOdpPostWrite($event);
    }
}
