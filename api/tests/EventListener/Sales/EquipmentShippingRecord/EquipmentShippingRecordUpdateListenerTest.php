<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\EquipmentShippingRecord;

use ApiPlatform\Metadata\Put;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\EventListener\Sales\EquipmentShippingRecord\EquipmentShippingRecordUpdateListener;
use App\Manager\Sales\EquipmentShippingRecordLineManager;
use App\Notifier\Sales\EquipmentShippingRecord\EquipmentShippingRecordNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use LegacyBundle\Manager\CustomerServiceRecordManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EquipmentShippingRecordUpdateListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testEquipmentShippingRecordUpdateOnStatus(): void
    {
        $this->expectException(BadRequestException::class);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('MOO_ESR')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_EQUIPMENT_SHIPPING_RECORD_REOPEN')->shouldBeCalledTimes(1)->willReturn(false);

        $equipmentShippingRecordClosed = new EquipmentShippingRecord();
        $equipmentShippingRecordClosed->setStatus(EquipmentShippingRecord::CLOSED);

        $customerServiceRecordManager = $this->prophesize(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord(Argument::type('integer'))->shouldBeCalledTimes(0);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(CustomerServiceRecordManager::class)->shouldBeCalledTimes(0);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes->set('previous_data', $equipmentShippingRecordClosed);

        $listener = new EquipmentShippingRecordUpdateListener($serviceLocatorProphecy->reveal());
        $listener->validateNotClosed(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $equipmentShippingRecordClosed));
    }

    public function testEquipmentShippingRecordReopenByMoo(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('MOO_ESR')->shouldBeCalledTimes(1)->willReturn(true);

        $equipmentShippingRecordClosed = new EquipmentShippingRecord();
        $equipmentShippingRecordClosed->setStatus(EquipmentShippingRecord::CLOSED);

        $customerServiceRecordManager = $this->prophesize(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord(Argument::type('integer'))->shouldBeCalledTimes(0);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(CustomerServiceRecordManager::class)->shouldBeCalledTimes(0);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes->set('previous_data', $equipmentShippingRecordClosed);

        $listener = new EquipmentShippingRecordUpdateListener($serviceLocatorProphecy->reveal());
        $listener->validateNotClosed(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $equipmentShippingRecordClosed));
    }

    public function testEquipmentShippingRecordUpdateToStatusClose(): void
    {
        $equipmentShippingRecordToClose = new EquipmentShippingRecord();
        $equipmentShippingRecordToClose->setStatus(EquipmentShippingRecord::CLOSED);
        $equipmentShippingRecordLine = new EquipmentShippingRecordLine();
        $equipmentShippingRecordLine->actualArrivalDate = new \DateTime('now');
        $equipmentShippingRecordToClose->addEquipmentShippingRecordLine($equipmentShippingRecordLine);

        $customerServiceRecordManager = $this->prophesize(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord(Argument::type('integer'))->shouldBeCalledTimes(0);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(CustomerServiceRecordManager::class)->shouldBeCalledTimes(0);

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes->set('_api_operation', new Put(name: 'update_equipment_shipping_record_status'));

        $listener = new EquipmentShippingRecordUpdateListener($serviceLocatorProphecy->reveal());
        $listener->allowedToClosed(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $equipmentShippingRecordToClose));
    }

    /**
     * @dataProvider notifyPickUpDateInformationChangesProvider
     */
    public function testNotifyPickUpDateInformationChanges(array $case): void
    {
        $controllerResult = $case['ESR'];
        $httpMethod = $case['METHOD'];
        $lines = $case['LINES'];
        $expectedEmails = $case['EXPECTED_EMAILS'];

        $notifierProphecy = $this->prophesize(EquipmentShippingRecordNotifier::class);
        $notifierProphecy
            ->onChangePickUpInformationNotification(Argument::type('array'))
            ->shouldBeCalledTimes($expectedEmails);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

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

        $listener = new EquipmentShippingRecordUpdateListener($serviceLocatorProphecy->reveal());

        if (!empty($lines)) {
            $reflection = new \ReflectionClass($listener);
            $property = $reflection->getProperty('linesChangedForPickUpInformation');
            $property->setAccessible(true);

            $linesChanged = [];
            $i = 1;

            foreach ($lines as $lineData) {
                $lineEntity = new EquipmentShippingRecordLine();

                $linesChanged['SN-00'.$i] = [
                    'equipmentShippingRecordLine' => $lineEntity,
                    'changes' => $lineData['changes'],
                ];

                ++$i;
            }

            $property->setValue($listener, $linesChanged);
        }

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy
            ->isMethod(Request::METHOD_PUT)
            ->willReturn(Request::METHOD_PUT === $httpMethod);

        $event = new ViewEvent(
            static::$kernel,
            $requestProphecy->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult
        );

        $listener->notifyPickUpDateInformationChanges($event);
    }

    public function notifyPickUpDateInformationChangesProvider(): \Generator
    {
        $equipmentShippingRecord = new EquipmentShippingRecord();

        yield 'PUT + ESR + 1 line (date changed) => 1 email' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_PUT,
            'LINES' => [
                [
                    'changes' => [
                        'estimatedPickUpDate' => [
                            'old' => new \DateTime('2025-01-01'),
                            'new' => new \DateTime('2025-01-02'),
                        ],
                    ],
                ],
            ],
            'EXPECTED_EMAILS' => 1,
        ]];
        yield 'PUT + ESR + 1 line (confirmation changed) => 1 email' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_PUT,
            'LINES' => [
                [
                    'changes' => [
                        'estimatedPickUpDateConfirmation' => [
                            'old' => false,
                            'new' => true,
                        ],
                    ],
                ],
            ],
            'EXPECTED_EMAILS' => 1,
        ]];
        yield 'PUT + ESR + 1 line (date + confirmation changed) => 1 email' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_PUT,
            'LINES' => [
                [
                    'changes' => [
                        'estimatedPickUpDate' => [
                            'old' => new \DateTime('2025-01-01'),
                            'new' => new \DateTime('2025-01-02'),
                        ],
                        'estimatedPickUpDateConfirmation' => [
                            'old' => false,
                            'new' => true,
                        ],
                    ],
                ],
            ],
            'EXPECTED_EMAILS' => 1,
        ]];
        yield 'PUT + ESR + 2 lines changed => 2 emails' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_PUT,
            'LINES' => [
                [
                    'changes' => [
                        'estimatedPickUpDate' => [
                            'old' => new \DateTime('2025-01-01'),
                            'new' => new \DateTime('2025-01-02'),
                        ],
                    ],
                ],
                [
                    'changes' => [
                        'estimatedPickUpDateConfirmation' => [
                            'old' => false,
                            'new' => true,
                        ],
                    ],
                ],
            ],
            'EXPECTED_EMAILS' => 2,
        ]];
        yield 'PUT + ESR + no lines changed => 0 email' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_PUT,
            'LINES' => [],
            'EXPECTED_EMAILS' => 0,
        ]];
        yield 'GET + ESR + lines changed => 0 email (wrong method)' => [[
            'ESR' => $equipmentShippingRecord,
            'METHOD' => Request::METHOD_GET,
            'LINES' => [
                [
                    'changes' => [
                        'estimatedPickUpDate' => [
                            'old' => null,
                            'new' => new \DateTime(),
                        ],
                    ],
                ],
            ],
            'EXPECTED_EMAILS' => 0,
        ]];
        yield 'PUT + invalid controller result + lines changed => 0 email' => [[
            'ESR' => new \stdClass(),
            'METHOD' => Request::METHOD_PUT,
            'LINES' => [
                [
                    'changes' => [
                        'estimatedPickUpDateConfirmation' => [
                            'old' => null,
                            'new' => true,
                        ],
                    ],
                ],
            ],
            'EXPECTED_EMAILS' => 0,
        ]];
    }

    /**
     * @dataProvider collectInformationProvider
     */
    public function testCollectInformationFillsLinesChangedForPickUpInformation(array $case): void
    {
        $oldDate = $case['OLD_DATE'];
        $newDate = $case['NEW_DATE'];
        $oldConfirmation = $case['OLD_CONFIRMATION'];
        $newConfirmation = $case['NEW_CONFIRMATION'];
        $expectedChangesKeys = $case['EXPECTED_CHANGES_KEYS'];

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setSerialNumber('SN-123');

        $line = new EquipmentShippingRecordLine();
        $line->equipmentRecord = $equipmentRecord;

        $line->estimatedPickUpDate = $newDate;
        $line->estimatedPickUpDateConfirmation = $newConfirmation;

        $equipmentShippingRecord = new EquipmentShippingRecord();
        $equipmentShippingRecord->addEquipmentShippingRecordLine($line);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects($this->once())
            ->method('computeChangeSets');

        $uow->method('getIdentityMap')
            ->willReturn([]);

        $uow->method('getEntityChangeSet')
            ->with($this->identicalTo($line))
            ->willReturn([
                'estimatedPickUpDate' => [$oldDate, $newDate],
                'estimatedPickUpDateConfirmation' => [$oldConfirmation, $newConfirmation],
            ]);

        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getUnitOfWork()->willReturn($uow);

        $lineManager = new EquipmentShippingRecordLineManager();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy
            ->get(EntityManagerInterface::class)
            ->shouldBeCalledTimes(1)
            ->willReturn($emProphecy->reveal());

        $serviceLocatorProphecy
            ->get(EquipmentShippingRecordLineManager::class)
            ->shouldBeCalledTimes(1)
            ->willReturn($lineManager);

        $listener = new EquipmentShippingRecordUpdateListener($serviceLocatorProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);

        $event = new ViewEvent(
            static::$kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $equipmentShippingRecord
        );

        $listener->collectInformation($event);

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('linesChangedForPickUpInformation');
        $property->setAccessible(true);

        $linesChanged = $property->getValue($listener);

        if (empty($expectedChangesKeys)) {
            // Aucun changement attendu => rien collecté
            self::assertSame([], $linesChanged);
        } else {
            self::assertCount(1, $linesChanged);
            self::assertArrayHasKey('SN-123', $linesChanged);

            $entry = $linesChanged['SN-123'];

            self::assertSame($line, $entry['equipmentShippingRecordLine']);
            self::assertSame($expectedChangesKeys, array_keys($entry['changes']));
        }
    }

    public function collectInformationProvider(): \Generator
    {
        yield 'pick-up date AND confirmation changed => pick-up info collected' => [[
            'OLD_DATE' => new \DateTime('2025-01-01'),
            'NEW_DATE' => new \DateTime('2025-01-02'),
            'OLD_CONFIRMATION' => false,
            'NEW_CONFIRMATION' => true,
            'EXPECTED_CHANGES_KEYS' => [
                'estimatedPickUpDate',
                'estimatedPickUpDateConfirmation',
            ],
        ]];

        yield 'only pick-up date changed' => [[
            'OLD_DATE' => new \DateTime('2025-01-01'),
            'NEW_DATE' => new \DateTime('2025-01-02'),
            'OLD_CONFIRMATION' => true,
            'NEW_CONFIRMATION' => true,
            'EXPECTED_CHANGES_KEYS' => [
                'estimatedPickUpDate',
            ],
        ]];

        yield 'only confirmation changed' => [[
            'OLD_DATE' => new \DateTime('2025-01-01'),
            'NEW_DATE' => new \DateTime('2025-01-01'),
            'OLD_CONFIRMATION' => false,
            'NEW_CONFIRMATION' => true,
            'EXPECTED_CHANGES_KEYS' => [
                'estimatedPickUpDateConfirmation',
            ],
        ]];

        yield 'no pick-up change => no pick-up info collected' => [[
            'OLD_DATE' => new \DateTime('2025-01-01'),
            'NEW_DATE' => new \DateTime('2025-01-01'),
            'OLD_CONFIRMATION' => true,
            'NEW_CONFIRMATION' => true,
            'EXPECTED_CHANGES_KEYS' => [],
        ]];
    }
}
