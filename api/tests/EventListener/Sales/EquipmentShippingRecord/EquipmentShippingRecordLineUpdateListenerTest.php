<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\EventListener\Sales\EquipmentShippingRecord\EquipmentShippingRecordLineUpdateListener;
use App\Manager\Sales\EquipmentShippingRecordLineManager;
use App\Notifier\Sales\EquipmentShippingRecord\EquipmentShippingRecordNotifier;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EquipmentShippingRecordLineUpdateListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider lineNotifyPickUpDateInformationChangesProvider
     */
    public function testLineNotifyPickUpDateInformationChanges(array $case): void
    {
        $controllerResult = $case['LINE'];
        $previous = $case['PREVIOUS'];
        $method = $case['METHOD'];
        $expectedEmails = $case['EXPECTED_EMAILS'];

        $lineManager = new EquipmentShippingRecordLineManager();

        $notifierProphecy = $this->prophesize(EquipmentShippingRecordNotifier::class);
        $notifierProphecy
            ->onChangePickUpInformationNotification(Argument::type('array'))
            ->shouldBeCalledTimes($expectedEmails);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $shouldCallManager =
            $controllerResult instanceof EquipmentShippingRecordLine
            && $previous instanceof EquipmentShippingRecordLine
            && Request::METHOD_PUT === $method;

        if ($shouldCallManager) {
            $serviceLocatorProphecy
                ->get(EquipmentShippingRecordLineManager::class)
                ->shouldBeCalledTimes(1)
                ->willReturn($lineManager);

            if ($expectedEmails > 0) {
                $serviceLocatorProphecy
                    ->get(EquipmentShippingRecordNotifier::class)
                    ->shouldBeCalledTimes(1)
                    ->willReturn($notifierProphecy->reveal());
            } else {
                $serviceLocatorProphecy
                    ->get(EquipmentShippingRecordNotifier::class)
                    ->shouldNotBeCalled();
            }
        } else {
            $serviceLocatorProphecy
                ->get(EquipmentShippingRecordLineManager::class)
                ->shouldNotBeCalled();
            $serviceLocatorProphecy
                ->get(EquipmentShippingRecordNotifier::class)
                ->shouldNotBeCalled();
        }

        $listener = new EquipmentShippingRecordLineUpdateListener($serviceLocatorProphecy->reveal());

        $request = new Request();
        $request->setMethod($method);
        $request->attributes->set('previous_data', $previous);

        $event = new ViewEvent(
            static::$kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult
        );

        $listener->notifyPickUpDateInformationChanges($event);
    }

    public function lineNotifyPickUpDateInformationChangesProvider(): \Generator
    {
        $previous1 = new EquipmentShippingRecordLine();
        $previous1->estimatedPickUpDate = new \DateTime('2025-01-01');
        $previous1->estimatedPickUpDateConfirmation = true;

        $line1 = new EquipmentShippingRecordLine();
        $line1->estimatedPickUpDate = new \DateTime('2025-01-02');
        $line1->estimatedPickUpDateConfirmation = true;
        yield 'PUT + line + date changed => 1 email' => [[
            'LINE' => $line1,
            'PREVIOUS' => $previous1,
            'METHOD' => Request::METHOD_PUT,
            'EXPECTED_EMAILS' => 1,
        ]];

        $previous2 = new EquipmentShippingRecordLine();
        $previous2->estimatedPickUpDate = new \DateTime('2025-01-01');
        $previous2->estimatedPickUpDateConfirmation = false;

        $line2 = new EquipmentShippingRecordLine();
        $line2->estimatedPickUpDate = new \DateTime('2025-01-01');
        $line2->estimatedPickUpDateConfirmation = true;
        yield 'PUT + line + confirmation changed => 1 email' => [[
            'LINE' => $line2,
            'PREVIOUS' => $previous2,
            'METHOD' => Request::METHOD_PUT,
            'EXPECTED_EMAILS' => 1,
        ]];

        $previous3 = new EquipmentShippingRecordLine();
        $previous3->estimatedPickUpDate = new \DateTime('2025-01-01');
        $previous3->estimatedPickUpDateConfirmation = false;

        $line3 = new EquipmentShippingRecordLine();
        $line3->estimatedPickUpDate = new \DateTime('2025-01-02');
        $line3->estimatedPickUpDateConfirmation = true;
        yield 'PUT + line + date & confirmation changed => 1 email' => [[
            'LINE' => $line3,
            'PREVIOUS' => $previous3,
            'METHOD' => Request::METHOD_PUT,
            'EXPECTED_EMAILS' => 1,
        ]];

        $previous4 = new EquipmentShippingRecordLine();
        $previous4->estimatedPickUpDate = new \DateTime('2025-01-01');
        $previous4->estimatedPickUpDateConfirmation = true;

        $line4 = new EquipmentShippingRecordLine();
        $line4->estimatedPickUpDate = new \DateTime('2025-01-01');
        $line4->estimatedPickUpDateConfirmation = true;
        yield 'PUT + line + no change => 0 email' => [[
            'LINE' => $line4,
            'PREVIOUS' => $previous4,
            'METHOD' => Request::METHOD_PUT,
            'EXPECTED_EMAILS' => 0,
        ]];

        $previous5 = clone $previous1;
        $line5 = clone $line1;
        yield 'GET + line + changed => 0 email (wrong method)' => [[
            'LINE' => $line5,
            'PREVIOUS' => $previous5,
            'METHOD' => Request::METHOD_GET,
            'EXPECTED_EMAILS' => 0,
        ]];

        $previous6 = $previous1;
        yield 'PUT + invalid controller result => 0 email' => [[
            'LINE' => new \stdClass(),
            'PREVIOUS' => $previous6,
            'METHOD' => Request::METHOD_PUT,
            'EXPECTED_EMAILS' => 0,
        ]];

        $line7 = $line1;
        yield 'PUT + line + invalid previous_data => 0 email' => [[
            'LINE' => $line7,
            'PREVIOUS' => new \stdClass(),
            'METHOD' => Request::METHOD_PUT,
            'EXPECTED_EMAILS' => 0,
        ]];
    }
}
