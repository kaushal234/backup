<?php

declare(strict_types=1);

namespace App\Tests\Command\HumanResources;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\HumanResources\EmployeeStaffingPreviousYearTotalsCommand;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\PositionCategory;
use App\Entity\Directory\PositionClassification;
use App\Entity\Report\ReportSnapshot;
use App\Repository\Report\ReportSnapshotRepository;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class EmployeeStaffingPreviousYearTotalsCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'api:human-resources:employee-staffing:end-of-year';

    private Application $application;

    protected function setUp(): void
    {
        Chronos::setTestNow('2006-01-02');
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecute()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $classificationsRepositoryMock = $this->createMock(EntityRepository::class);
        $businessUnitRepositoryMock = $this->createMock(EntityRepository::class);
        $reportSnapshotRepositoryMock = $this->getMockBuilder(ReportSnapshotRepository::class)->disableOriginalConstructor()->onlyMethods(['findSnapshot'])->getMock();

        /** @var BusinessUnit $businessUnit */
        $businessUnit = $this->getInstanceWithId(BusinessUnit::class, 12);
        $businessUnit->setName('Alvest IT');
        $businessUnitRepositoryMock->expects($this->once())->method('findAll')->willReturn([$businessUnit]);

        /** @var PositionCategory $positionCategory */
        $positionCategory = $this->getInstanceWithId(PositionCategory::class, 42);
        $positionCategory->name = 'Developer';
        $classification = new PositionClassification();
        $classification->correction = -5;
        $classification->positionCategory = $positionCategory;

        $classificationsRepositoryMock->expects($this->once())->method('findBy')->with(['businessUnit' => $businessUnit])->willReturn([$classification]);

        $snapshot = new ReportSnapshot();
        $snapshot->metadata = ['xIris' => ['Developer' => '/position_categories/42']];
        $snapshot->xTotals = ['Developer' => 1000.0];
        $reportSnapshotRepositoryMock->expects($this->once())->method('findSnapshot')->with(
            '/people',
            'businessUnit.positionClassifications.positionCategory.name',
            'contractType.name',
            ['entity' => '/business_units/12'],
            new \DateTime('2006-01-02 00:00:00')
        )->willReturn($snapshot);

        $entityManagerProphecy->getRepository(PositionClassification::class)->shouldBeCalledTimes(1)->willReturn($classificationsRepositoryMock);
        $entityManagerProphecy->getRepository(BusinessUnit::class)->shouldBeCalledTimes(1)->willReturn($businessUnitRepositoryMock);
        $entityManagerProphecy->getRepository(ReportSnapshot::class)->shouldBeCalledTimes(1)->willReturn($reportSnapshotRepositoryMock);
        $entityManagerProphecy->persist($classification)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $this->application->addCommand(new EmployeeStaffingPreviousYearTotalsCommand(
            static::getContainer()->get(IriConverterInterface::class),
            $entityManagerProphecy->reveal()
        ));
        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        self::assertSame(0, $commandTester->execute(['command' => self::COMMAND], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]));
        self::assertSame(995.0, $classification->previousYearCorrectedTotal);
        self::assertStringContainsString('[info] Processing snapshot value for Alvest IT', $commandTester->getDisplay());

        Chronos::setTestNow();
    }

    private function getInstanceWithId(string $class, int $id): object
    {
        $object = new $class();
        $reflectionProperty = (new \ReflectionClass($object))->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $id);

        return $object;
    }
}
