<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProcessor\Manufacturing;

use ApiPlatform\Metadata\Post;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\ION\DataProcessor\Manufacturing\EstimatedGreenTagDateEquipmentRecordDataProcessor;
use App\ION\Dto\Manufacturing\EstimatedGreenTagDateEquipmentRecord;
use App\Manager\EquipmentRecordManager;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class EstimatedGreenTagDateEquipmentRecordDataProcessorTest extends TestCase
{
    use ProphecyTrait;

    private $equipmentRecordRepositoryMock;
    private $entityManagerProphecy;
    private $equipmentRecordNotifierProphecy;
    private $equipmentRecordManagerProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipmentRecordRepositoryMock = $this->getMockBuilder(EquipmentRecordRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBy'])->getMock();
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $this->equipmentRecordNotifierProphecy = $this->prophesize(EquipmentRecordNotifier::class);
        $this->equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
    }

    public function testPersistAndSendGapAlert()
    {
        $newEstimatedGreenTagDate = new \DateTime('2022-05-02');
        $previousEstimatedGreenTagDate = new \DateTime('2020-01-01');

        $equipmentRecordData = new EstimatedGreenTagDateEquipmentRecord();
        $equipmentRecordData->equipmentRecord = 'equipment_record_id';
        $equipmentRecordData->estimatedGreenTagDate = $newEstimatedGreenTagDate->format('Y-m-d');

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setProjectNumber('equipment_record_id');
        $equipmentRecord->setEstimatedGreenTagDate($previousEstimatedGreenTagDate);

        $this->equipmentRecordRepositoryMock->expects($this->once())->method('findOneBy')->with(['projectNumber' => $equipmentRecordData->equipmentRecord])->willReturn($equipmentRecord);
        $this->equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert($equipmentRecord, $previousEstimatedGreenTagDate)->shouldBeCalledOnce()->willReturn(true);
        $this->equipmentRecordNotifierProphecy->sendAlertEstimatedGreenTagDateGap($equipmentRecord, '2020-01-01')->shouldBeCalledOnce();

        $dataPersister = new EstimatedGreenTagDateEquipmentRecordDataProcessor(
            $this->equipmentRecordRepositoryMock,
            $this->entityManagerProphecy->reveal(),
            $this->equipmentRecordNotifierProphecy->reveal(),
            $this->equipmentRecordManagerProphecy->reveal()
        );

        $result = $dataPersister->process($equipmentRecordData, new Post());

        $this->assertSame($equipmentRecordData, $result);
        $this->assertSame($equipmentRecord->getEstimatedGreenTagDate()->format('Y-m-d'), $newEstimatedGreenTagDate->format('Y-m-d'), 'EstimatedGreenTagDate updated');

        $this->entityManagerProphecy->persist(Argument::type(EquipmentRecord::class))->shouldHaveBeenCalled();
        $this->entityManagerProphecy->flush()->shouldHaveBeenCalled();
        $this->equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert($equipmentRecord, $previousEstimatedGreenTagDate)->shouldHaveBeenCalledOnce();
        $this->equipmentRecordNotifierProphecy->sendAlertEstimatedGreenTagDateGap($equipmentRecord, '2020-01-01')->shouldHaveBeenCalledOnce();
    }

    /**
     * @dataProvider DataProviderNotPersistAndNotSendAlert
     */
    public function testPersistAndNotSendGapAlert(string $newEstimatedGreenTagDate, ?string $previousEstimatedGreenTagDate, string $customerName)
    {
        $newEstimatedGreenTagDate = new \DateTime($newEstimatedGreenTagDate);
        $previousEstimatedGreenTagDate = null === $previousEstimatedGreenTagDate ? null : new \DateTime($previousEstimatedGreenTagDate);

        $equipmentRecordData = new EstimatedGreenTagDateEquipmentRecord();
        $equipmentRecordData->equipmentRecord = 'equipment_record_id';
        $equipmentRecordData->estimatedGreenTagDate = $newEstimatedGreenTagDate->format('Y-m-d');

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setProjectNumber('equipment_record_id');
        $equipmentRecord->setEstimatedGreenTagDate($previousEstimatedGreenTagDate);
        $equipmentRecord->setBuyer((new Customer())->setName($customerName));

        $this->equipmentRecordRepositoryMock->expects($this->once())->method('findOneBy')->with(['projectNumber' => $equipmentRecordData->equipmentRecord])->willReturn($equipmentRecord);
        $this->equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert($equipmentRecord, $previousEstimatedGreenTagDate)->shouldBeCalledOnce()->willReturn(false);

        $dataPersister = new EstimatedGreenTagDateEquipmentRecordDataProcessor(
            $this->equipmentRecordRepositoryMock,
            $this->entityManagerProphecy->reveal(),
            $this->equipmentRecordNotifierProphecy->reveal(),
            $this->equipmentRecordManagerProphecy->reveal()
        );

        $result = $dataPersister->process($equipmentRecordData, new Post());
        $this->assertSame($equipmentRecord->getEstimatedGreenTagDate()->format('Y-m-d'), $newEstimatedGreenTagDate->format('Y-m-d'));

        $this->assertSame($equipmentRecordData, $result);

        $this->entityManagerProphecy->persist(Argument::type(EquipmentRecord::class))->shouldHaveBeenCalled();
        $this->entityManagerProphecy->flush()->shouldHaveBeenCalled();
        $this->equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert($equipmentRecord, $previousEstimatedGreenTagDate)->shouldHaveBeenCalledOnce();
        $this->equipmentRecordNotifierProphecy->sendAlertEstimatedGreenTagDateGap($equipmentRecord, '2020-01-01')->shouldNotHaveBeenCalled();
    }

    /**
     * @dataProvider dataProviderNotPersist
     */
    public function testNotPersistAndNotSendAlert(?EquipmentRecord $equipmentRecord, ?\DateTime $greenTagDate, ?\DateTime $previousEstimatedGreenTagDate, \DateTime $updatedEstimatedGreenTagDate)
    {
        $equipmentRecordData = new EstimatedGreenTagDateEquipmentRecord();
        $equipmentRecordData->equipmentRecord = 'equipment_record_id';
        $equipmentRecordData->estimatedGreenTagDate = $updatedEstimatedGreenTagDate->format('Y-m-d');

        if (null !== $equipmentRecord) {
            $equipmentRecord->setProjectNumber($equipmentRecordData->equipmentRecord);
            $equipmentRecord->setGreenTagDate($greenTagDate);
            $equipmentRecord->setEstimatedGreenTagDate($previousEstimatedGreenTagDate);
            $this->equipmentRecordRepositoryMock->expects($this->once())->method('findOneBy')->with(['projectNumber' => $equipmentRecordData->equipmentRecord])->willReturn($equipmentRecord);
        } else {
            $this->equipmentRecordRepositoryMock->expects($this->once())->method('findOneBy')->with(['projectNumber' => $equipmentRecordData->equipmentRecord])->willReturn(null);
        }

        $dataPersister = new EstimatedGreenTagDateEquipmentRecordDataProcessor(
            $this->equipmentRecordRepositoryMock,
            $this->entityManagerProphecy->reveal(),
            $this->equipmentRecordNotifierProphecy->reveal(),
            $this->equipmentRecordManagerProphecy->reveal()
        );

        $result = $dataPersister->process($equipmentRecordData, new Post());

        $this->assertSame($equipmentRecordData, $result);

        if (null === $equipmentRecord) {
            $this->entityManagerProphecy->persist(Argument::type(EquipmentRecord::class))->shouldNotHaveBeenCalled();
            $this->entityManagerProphecy->flush()->shouldNotHaveBeenCalled();
            $this->equipmentRecordManagerProphecy->updatedEstimatedGreenTagDateNeedsToSendGapAlert(Argument::cetera())->shouldNotHaveBeenCalled();
            $this->equipmentRecordNotifierProphecy->sendAlertEstimatedGreenTagDateGap(Argument::cetera())->shouldNotHaveBeenCalled();
        }
    }

    public function dataProviderNotPersist()
    {
        $equipmentRecord = new EquipmentRecord();

        return [
            'EquipmentRecord not found' => [null, null, new \DateTime(), new \DateTime('+20 days')],
            'Already green tag' => [$equipmentRecord, new \DateTime(), new \DateTime(), new \DateTime('+20 days')],
            'EstimatedGreenTagDate not changed' => [$equipmentRecord, null, new \DateTime(), new \DateTime()],
        ];
    }

    public function DataProviderNotPersistAndNotSendAlert()
    {
        return [
            'EquipmentRecord with no previous Estimated green tag date' => ['2022-05-02', null, 'BABAR'],
            'EquipmentRecord with Alvest customer' => ['2022-05-02', '2020-05-02', Customer::CUSTOMER_DEMO],
        ];
    }
}
