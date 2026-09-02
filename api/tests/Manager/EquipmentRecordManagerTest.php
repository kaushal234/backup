<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\OrderLine;
use App\Entity\Sales\OrderToFactory;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\Manager\EquipmentRecordManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EquipmentRecordManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestCases
     */
    public function testUpdatedEstimatedGreenTagDateNeedsToSendGapAlert($previousDate, $currentDate, $expectedResult, ?\DateTime $firstGreenTagDate = null, string $customerName = 'Rodolphe Free', bool $isLinkToASol = true, ?\DateTime $factoryPromisedDate = null): void
    {
        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setEstimatedGreenTagDate($currentDate);
        $equipmentRecord->setFirstGreenTagDate($firstGreenTagDate);
        $equipmentRecord->setBuyer((new Customer())->setName($customerName));

        $orderLine = new OrderLine();
        $orderToFactory = new OrderToFactory();
        $orderToFactory->factoryPromisedDeliveryDate = $factoryPromisedDate;
        $orderLine->addFactoryOrder($orderToFactory);
        if ($isLinkToASol) {
            $equipmentRecord->orderFactory = $orderToFactory;
        }

        $equipmentRecordManager = new EquipmentRecordManager($this->prophesize(CachedIONItemDataProvider::class)->reveal(), $this->prophesize(ResourceMetadataCollectionFactoryInterface::class)->reveal(), $this->prophesize(EntityManagerInterface::class)->reveal());
        $result = $equipmentRecordManager->updatedEstimatedGreenTagDateNeedsToSendGapAlert($equipmentRecord, $previousDate);

        $this->assertSame($expectedResult, $result);
    }

    public function provideTestCases(): \Generator
    {
        yield from $this->provideTestCasesForAlert();
        yield from $this->provideTestCasesForNoAlert();
    }

    public function provideTestCasesForAlert(): \Generator
    {
        yield 'NotFirstGreenTaggedEquipmentRecord with Gap between 10 and 20 days AND previous EGTD is less or equal than 7 days' => [new \DateTime('+ 7 days'), new \DateTime('+ 18 days'), true];
        yield 'NotFirstGreenTaggedEquipmentRecord with Gap between 20 and 30 days AND previous EGTD is less or equal than 30 days' => [new \DateTime('+ 20 days'), new \DateTime('+ 41 days'), true];
        yield 'NotFirstGreenTaggedEquipmentRecord with Gap is more than 30 days' => [new \DateTime('+ 7 days'), new \DateTime('+ 38 days'), true];
    }

    public function provideTestCasesForNoAlert(): \Generator
    {
        yield 'EstimatedGreenTagDate not set and not changed' => [null, null, false];
        yield 'EstimatedGreenTagDate is set for the first time' => [null, new \DateTime(), false];
        yield 'EstimatedGreenTagDate is reset to null' => [new \DateTime('2040-01-01'), new \DateTime('2040-01-01'), false];
        yield 'EstimatedGreenTagDate not changed' => [new \DateTime('2040-01-01'), new \DateTime('2040-01-01'), false];

        yield 'FirstGreenTaggedEquipmentRecord with Gap between 10 and 20 days AND previous EGTD is less or equal than 7 days' => [new \DateTime('+ 7 days'), new \DateTime('+ 18 days'), false, new \DateTime()];
        yield 'FirstGreenTaggedEquipmentRecord with Gap between 20 and 30 days AND previous EGTD is less or equal than 30 days' => [new \DateTime('+ 20 days'), new \DateTime('+ 41 days'), false, new \DateTime()];
        yield 'FirstGreenTaggedEquipmentRecord with Gap more than 30 days' => [new \DateTime('+ 7 days'), new \DateTime('+ 38 days'), false, new \DateTime()];

        yield 'Gap less than 10 days' => [new \DateTime('+ 7 days'), new \DateTime('+ 17 days'), false];
        yield 'Gap between 10 and 20 days AND previous EGTD is greater than 7 days' => [new \DateTime('+ 8 days'), new \DateTime('+ 19 days'), false];
        yield 'Gap between 20 and 30 days AND previous EGTD is greater than 30 days' => [new \DateTime('+ 31 days'), new \DateTime('+ 52 days'), false];

        yield 'Alert should not be send if customer is from Alvest' => [new \DateTime('+ 7 days'), new \DateTime('+ 38 days'), false, null, Customer::CUSTOMER_DEMO];
        yield 'Alert should not be send if ER is not linked to a SOL' => [new \DateTime('+ 7 days'), new \DateTime('+ 38 days'), false, null, 'RAYMOND POULIDOR', false];

        yield 'Alert should not be send if ER factory order promised Delivery date is superior to new EGTD' => [new \DateTime('+ 7 days'), new \DateTime('+ 38 days'), false, null, 'OSS 117', true, new \DateTime('+ 39 days')];
    }
}
