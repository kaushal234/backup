<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\EventListener\Sales\EquipmentShippingRecord\EquipmentShippingRecordUpdateListener;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EquipmentShippingRecordUpdateListenerOnChangePickUpDateInformationChangesTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider onChangePickUpDateInformationChangesProvider
     */
    public function testOnChangePickUpDateInformationChanges(array $case): void
    {
        $equipmentShippingRecord = $case['EQUIPMENT_SHIPPING_RECORD'];
        $httpMethod = $case['HTTP_METHOD'];

        $featureFlagEnabled = $case['FEATURE_FLAG_ENABLED'];
        $linesChangedForPickUpInformation = $case['LINES_CHANGED_FOR_PICK_UP_INFORMATION'];

        $expectedConfirmationAfter = $case['EXPECTED_CONFIRMATION_AFTER'];

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy
            ->isGranted('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION')
            ->willReturn($featureFlagEnabled);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $isPutOnEsr = $equipmentShippingRecord instanceof EquipmentShippingRecord && Request::METHOD_PUT === $httpMethod;

        $hasLinesChanged = !empty($linesChangedForPickUpInformation);

        $shouldFetchSecurity = $isPutOnEsr && $hasLinesChanged;

        if ($shouldFetchSecurity) {
            $serviceLocatorProphecy->get(Security::class)
                ->shouldBeCalledTimes(1)
                ->willReturn($securityProphecy->reveal());
        } else {
            $serviceLocatorProphecy->get(Security::class)
                ->shouldNotBeCalled();
        }

        $shouldFetchEntityManager = $isPutOnEsr && $hasLinesChanged && !$featureFlagEnabled;

        if ($shouldFetchEntityManager) {
            $serviceLocatorProphecy->get(EntityManagerInterface::class)
                ->shouldBeCalledTimes(1)
                ->willReturn($entityManagerProphecy->reveal());
        } else {
            $serviceLocatorProphecy->get(EntityManagerInterface::class)
                ->shouldNotBeCalled();
        }

        $shouldFlush = $shouldFetchEntityManager && false === $expectedConfirmationAfter;

        if ($shouldFlush) {
            $entityManagerProphecy
                ->flush()
                ->shouldBeCalledTimes(1);
        } else {
            $entityManagerProphecy
                ->flush()
                ->shouldNotBeCalled();
        }

        $listener = new EquipmentShippingRecordUpdateListener($serviceLocatorProphecy->reveal());

        if ($hasLinesChanged) {
            $this->setPrivateProperty($listener, 'linesChangedForPickUpInformation', $linesChangedForPickUpInformation);
        }

        $request = new Request();
        $request->setMethod($httpMethod);

        $event = new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $equipmentShippingRecord);

        /** @var EquipmentShippingRecordLine $equipmentShippingRecordLine */
        $equipmentShippingRecordLine = $case['EQUIPMENT_SHIPPING_RECORD_LINE'];

        $listener->onChangePickUpDateInformationChanges($event);

        self::assertSame(
            $expectedConfirmationAfter,
            $equipmentShippingRecordLine->estimatedPickUpDateConfirmation,
            'Estimated pick up date confirmation did not match expected value after onChangePickUpDateInformationChanges.'
        );
    }

    public function onChangePickUpDateInformationChangesProvider(): \Generator
    {
        $lineCase1 = new EquipmentShippingRecordLine();
        $lineCase1->estimatedPickUpDateConfirmation = true;

        yield 'PUT + pick up date changed + feature flag disabled => confirmation set to false' => [[
            'EQUIPMENT_SHIPPING_RECORD' => new EquipmentShippingRecord(),
            'HTTP_METHOD' => Request::METHOD_PUT,
            'FEATURE_FLAG_ENABLED' => false,
            'EQUIPMENT_SHIPPING_RECORD_LINE' => $lineCase1,
            'LINES_CHANGED_FOR_PICK_UP_INFORMATION' => [
                'equipment_serial_number_000001' => [
                    'equipmentShippingRecordLine' => $lineCase1,
                    'changes' => [
                        'estimatedPickUpDate' => [
                            'old' => new \DateTime('2025-01-01'),
                            'new' => new \DateTime('2025-01-02'),
                        ],
                    ],
                ],
            ],
            'EXPECTED_CONFIRMATION_AFTER' => false,
        ]];

        $lineCase2 = new EquipmentShippingRecordLine();
        $lineCase2->estimatedPickUpDateConfirmation = true;

        yield 'PUT + pick up date changed + feature flag enabled => confirmation unchanged' => [[
            'EQUIPMENT_SHIPPING_RECORD' => new EquipmentShippingRecord(),
            'HTTP_METHOD' => Request::METHOD_PUT,
            'FEATURE_FLAG_ENABLED' => true,
            'EQUIPMENT_SHIPPING_RECORD_LINE' => $lineCase2,
            'LINES_CHANGED_FOR_PICK_UP_INFORMATION' => [
                'equipment_serial_number_000002' => [
                    'equipmentShippingRecordLine' => $lineCase2,
                    'changes' => [
                        'estimatedPickUpDate' => [
                            'old' => new \DateTime('2025-01-01'),
                            'new' => new \DateTime('2025-01-02'),
                        ],
                    ],
                ],
            ],
            'EXPECTED_CONFIRMATION_AFTER' => true,
        ]];

        $lineCase3 = new EquipmentShippingRecordLine();
        $lineCase3->estimatedPickUpDateConfirmation = true;

        yield 'PUT + no pick up date changes => early return, confirmation unchanged' => [[
            'EQUIPMENT_SHIPPING_RECORD' => new EquipmentShippingRecord(),
            'HTTP_METHOD' => Request::METHOD_PUT,
            'FEATURE_FLAG_ENABLED' => false, // irrelevant here because the listener returns before fetching Security
            'EQUIPMENT_SHIPPING_RECORD_LINE' => $lineCase3,
            'LINES_CHANGED_FOR_PICK_UP_INFORMATION' => [],
            'EXPECTED_CONFIRMATION_AFTER' => true,
        ]];
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
