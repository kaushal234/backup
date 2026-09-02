<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\EquipmentShippingRecord;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\EventListener\Sales\EquipmentShippingRecord\EquipmentShippingRecordCreationListener;
use App\Notifier\Sales\EquipmentShippingRecord\EquipmentShippingRecordNotifier;
use LegacyBundle\Manager\EquipmentRecordManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EquipmentShippingRecordCreationListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider createEventProvider
     */
    public function testEquipmentShippingRecordCreation(object $controllerResult, bool $isPostMethod, int $expectedCall): void
    {
        $equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
        $equipmentRecordManagerProphecy->updateEsrIdProperty(Argument::type(EquipmentShippingRecord::class), Argument::type(EquipmentRecord::class))->shouldBeCalledTimes($expectedCall);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(EquipmentRecordManager::class)->shouldBeCalledTimes($expectedCall)->willReturn($equipmentRecordManagerProphecy->reveal());

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_POST)->willReturn($isPostMethod);

        $listener = new EquipmentShippingRecordCreationListener($serviceLocatorProphecy->reveal());
        $listener->updateLegacyEquipmentRecord(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $controllerResult));
    }

    public function createEventProvider(): \Generator
    {
        $equipmentShippingRecordWithoutLines = new EquipmentShippingRecord();

        $equipmentShippingRecordWithOneLines = new EquipmentShippingRecord();
        $newEquipmentShippingRecordLine1 = new EquipmentShippingRecordLine();
        $equipmentRecord1 = new EquipmentRecord();
        $newEquipmentShippingRecordLine1->equipmentRecord = $equipmentRecord1;
        $equipmentShippingRecordWithOneLines->addEquipmentShippingRecordLine($newEquipmentShippingRecordLine1);
        $equipmentShippingRecordWithTwoLines = new EquipmentShippingRecord();
        $newEquipmentShippingRecordLine2 = new EquipmentShippingRecordLine();
        $equipmentRecord2 = new EquipmentRecord();
        $newEquipmentShippingRecordLine2->equipmentRecord = $equipmentRecord2;
        $equipmentShippingRecordWithTwoLines->addEquipmentShippingRecordLine($newEquipmentShippingRecordLine1)->addEquipmentShippingRecordLine($newEquipmentShippingRecordLine2);

        yield 'updateEsrIdProperty without line on POST' => [$equipmentShippingRecordWithoutLines, true, 0];
        yield 'updateEsrIdProperty with one Line on POST' => [$equipmentShippingRecordWithOneLines, true, 1];
        yield 'updateEsrIdProperty with 2 Lines on POST' => [$equipmentShippingRecordWithTwoLines, true, 2];
        yield 'Not on DELETE method' => [$equipmentShippingRecordWithoutLines, false, 0];
        yield 'Not on GET method' => [$equipmentShippingRecordWithoutLines, false, 0];
        yield 'Not a EquipmentShippingRecord' => [new \stdClass(), false, 0];
        yield 'Not a POST method and not a EquipmentShippingRecord' => [new \stdClass(), false, 0];
    }

    /**
     * @dataProvider newEquipmentShippingRecordNotificationProvider
     */
    public function testNewEquipmentShippingRecordNotification(array $case): void
    {
        $controllerResult = $case['ESR'];
        $httpMethod = $case['METHOD'];
        $shouldSendNotification = $case['NotificationSent'];

        $expectedCall = $shouldSendNotification ? 1 : 0;

        $notifierProphecy = $this->prophesize(EquipmentShippingRecordNotifier::class);
        $notifierProphecy
            ->sendNewEquipmentShippingRecordNotification(Argument::type(EquipmentShippingRecord::class))
            ->shouldBeCalledTimes($expectedCall);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        if ($shouldSendNotification) {
            $serviceLocatorProphecy
                ->get(EquipmentShippingRecordNotifier::class)
                ->shouldBeCalledTimes(1)
                ->willReturn($notifierProphecy->reveal());
        } else {
            $serviceLocatorProphecy
                ->get(EquipmentShippingRecordNotifier::class)
                ->shouldNotBeCalled();
        }

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy
            ->isMethod(Request::METHOD_POST)
            ->willReturn(Request::METHOD_POST === $httpMethod);

        $listener = new EquipmentShippingRecordCreationListener($serviceLocatorProphecy->reveal());

        $event = new ViewEvent(
            static::$kernel,
            $requestProphecy->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult
        );

        $listener->newEquipmentShippingRecordNotification($event);
    }

    public function newEquipmentShippingRecordNotificationProvider(): \Generator
    {
        $equipmentShippingRecord = new EquipmentShippingRecord();

        yield 'POST + ESR => notification sent' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_POST,
            'NotificationSent' => true,
        ]];

        yield 'GET + ESR => no notification' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_GET,
            'NotificationSent' => false,
        ]];

        yield 'POST + invalid data => no notification' => [[
            'ESR' => new \stdClass(),
            'METHOD' => Request::METHOD_POST,
            'NotificationSent' => false,
        ]];

        yield 'GET + invalid data => no notification' => [[
            'ESR' => new \stdClass(),
            'METHOD' => Request::METHOD_GET,
            'NotificationSent' => false,
        ]];
    }
}
