<?php

declare(strict_types=1);

namespace App\Tests\Command\Support;

use App\Command\Support\EstimatedGreenTagQuantityReportCommand;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport;
use App\Repository\Directory\LocationRepository;
use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EstimatedGreenTagQuantityReportCommandTest extends TestCase
{
    private EquipmentRecordRepository&MockObject $equipmentRecordRepository;
    private LocationRepository&MockObject $locationRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->equipmentRecordRepository = $this->createMock(EquipmentRecordRepository::class);
        $this->locationRepository = $this->createMock(LocationRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testExecuteWithGroupedRecords(): void
    {
        $today = new \DateTime('2024-01-15');

        $location = $this->createMock(Location::class);
        $location->method('getId')->willReturn(42);

        $record1 = $this->createMock(EquipmentRecord::class);
        $record1->method('getEstimatedGreenTagDate')->willReturn($today);
        $record1->method('getManufacturerLocation')->willReturn($location);

        $record2 = $this->createMock(EquipmentRecord::class);
        $record2->method('getEstimatedGreenTagDate')->willReturn($today);
        $record2->method('getManufacturerLocation')->willReturn($location);

        $records = [$record1, $record2];

        $this->input
            ->method('getOption')
            ->with('month')
            ->willReturn('2024-01-01');

        $this->equipmentRecordRepository
            ->method('getEquipmentRecordsEstimatedGreenTagByMonth')
            ->willReturn($records);

        $this->locationRepository
            ->method('find')
            ->with(42)
            ->willReturn($location);

        $this->entityManager
            ->method('getRepository')
            ->willReturnCallback(function () {
                $metadata = new \Doctrine\ORM\Mapping\ClassMetadata(EstimatedGreenTagQuantityReport::class);

                $repo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
                    ->setConstructorArgs([$this->entityManager, $metadata])
                    ->onlyMethods(['findOneBy'])
                    ->getMock();

                $repo->method('findOneBy')->willReturn(null);

                return $repo;
            });

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function ($report) use ($today, $location, $records) {
                if (!$report instanceof EstimatedGreenTagQuantityReport) {
                    return false;
                }

                if ($report->day->format('Y-m-d') !== $today->format('Y-m-d')) {
                    return false;
                }

                if ($report->manufacturerLocation !== $location) {
                    return false;
                }

                if (\count($report->getEquipmentRecords()) !== \count($records)) {
                    return false;
                }

                return true;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $command = new EstimatedGreenTagQuantityReportCommand(
            $this->equipmentRecordRepository,
            $this->locationRepository,
            $this->entityManager
        );

        $result = $command->run($this->input, $this->output);
        $this->assertSame(Command::SUCCESS, $result);
    }

    public function testExecuteWithNoRecords(): void
    {
        $this->input
            ->method('getOption')
            ->with('month')
            ->willReturn('2024-01-01');

        $this->equipmentRecordRepository
            ->method('getEquipmentRecordsEstimatedGreenTagByMonth')
            ->willReturn([]);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $command = new EstimatedGreenTagQuantityReportCommand(
            $this->equipmentRecordRepository,
            $this->locationRepository,
            $this->entityManager
        );

        $result = $command->run($this->input, $this->output);
        $this->assertSame(Command::SUCCESS, $result);
    }

    public function testExecuteWithNullLocation(): void
    {
        $today = new \DateTime('2024-01-15');

        $record = $this->createMock(EquipmentRecord::class);
        $record->method('getEstimatedGreenTagDate')->willReturn($today);
        $record->method('getManufacturerLocation')->willReturn(null);

        $this->input
            ->method('getOption')
            ->with('month')
            ->willReturn('2024-01-01');

        $this->equipmentRecordRepository
            ->method('getEquipmentRecordsEstimatedGreenTagByMonth')
            ->willReturn([$record]);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $command = new EstimatedGreenTagQuantityReportCommand(
            $this->equipmentRecordRepository,
            $this->locationRepository,
            $this->entityManager
        );

        $result = $command->run($this->input, $this->output);
        $this->assertSame(Command::SUCCESS, $result);
    }
}
