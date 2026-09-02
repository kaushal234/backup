<?php

declare(strict_types=1);

namespace App\Tests\Command\HumanResources;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\HumanResources\EmployeeStaffingSnapshotCommand;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\Report\ReportSnapshot;
use App\Report\Report;
use App\Report\ReportGenerator;
use App\Report\ReportSnapshotFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class EmployeeStaffingSnapshotCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'api:human-resources:employee-staffing:snapshot';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecute()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $reportGeneratorProphecy = $this->prophesize(ReportGenerator::class);

        $reportGeneratorProphecy->getReport(
            '/people',
            'businessUnit.positionClassifications.positionCategory.name',
            'contractType.name',
            []
        )->shouldBeCalledTimes(1)->willReturn(new Report('/people', 'businessUnit.positionClassifications.positionCategory.name', 'contractType.name'));

        $entityManagerProphecy->persist(Argument::that(static fn (ReportSnapshot $snapshot) => [] === $snapshot->options))->shouldBeCalledTimes(1);

        foreach ([Division::class => '/divisions', SubDivision::class => '/sub_divisions', Region::class => '/regions', BusinessUnit::class => '/business_units'] as $entity => $iri) {
            $object = new $entity();
            $reflectionProperty = (new \ReflectionClass($object))->getProperty('id');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, 45);

            $repository = $this->createMock(EntityRepository::class);
            $repository->expects($this->once())->method('findAll')->willReturn([$object]);
            $entityManagerProphecy->getRepository($entity)->shouldBeCalledTimes(1)->willReturn($repository);

            $reportOptions = ['entity' => $iri.'/45'];

            $reportGeneratorProphecy->getReport(
                '/people',
                'businessUnit.positionClassifications.positionCategory.name',
                'contractType.name',
                Argument::that(static fn (array $options) => $options === $reportOptions)
            )->shouldBeCalledTimes(1)->willReturn(new Report('/people', 'businessUnit.positionClassifications.positionCategory.name', 'contractType.name'));

            $entityManagerProphecy->persist(Argument::that(static fn (ReportSnapshot $snapshot) => $snapshot->options === $reportOptions))->shouldBeCalledTimes(1);
        }

        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $this->application->addCommand(new EmployeeStaffingSnapshotCommand(
            $reportGeneratorProphecy->reveal(),
            static::getContainer()->get(IriConverterInterface::class),
            $entityManagerProphecy->reveal(),
            static::getContainer()->get(ReportSnapshotFactory::class)
        ));
        $command = $this->application->find(self::COMMAND);

        $commandTester = new CommandTester($command);
        self::assertSame(0, $commandTester->execute(['command' => self::COMMAND], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]));

        $display = $commandTester->getDisplay();
        self::assertStringContainsString('[info] Snapshot created for All', $display);
        self::assertStringContainsString('[info] Snapshot created for /divisions/45', $display);
        self::assertStringContainsString('[info] Snapshot created for /sub_divisions/45', $display);
        self::assertStringContainsString('[info] Snapshot created for /regions/45', $display);
        self::assertStringContainsString('[info] Snapshot created for /business_units/45', $display);
    }
}
