<?php

declare(strict_types=1);

namespace App\Command\HumanResources;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Report\Handler\HumanResources\EmployeeStaffingContractByPositionCategoryHandler;
use App\Report\ReportGenerator;
use App\Report\ReportSnapshotFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:employee-staffing:snapshot')]
class EmployeeStaffingSnapshotCommand extends Command
{
    private readonly ReportGenerator $reportGenerator;
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;
    private readonly ReportSnapshotFactory $reportSnapshotFactory;

    public function __construct(ReportGenerator $reportGenerator, IriConverterInterface $iriConverter, EntityManagerInterface $entityManager, ReportSnapshotFactory $reportSnapshotFactory)
    {
        parent::__construct();
        $this->reportGenerator = $reportGenerator;
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
        $this->reportSnapshotFactory = $reportSnapshotFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);

        $entities = [];
        foreach ([Division::class, SubDivision::class, Region::class, BusinessUnit::class] as $entity) {
            $entities[] = $this->entityManager->getRepository($entity)->findAll();
        }
        $entities = array_merge(...$entities);

        $resource = $this->iriConverter->getIriFromResource(People::class, UrlGeneratorInterface::ABS_PATH, new GetCollection());

        foreach ([null, ...$entities] as $entity) {
            $options = null !== $entity ? ['entity' => $iri = $this->iriConverter->getIriFromResource($entity)] : [];
            $report = $this->reportGenerator->getReport(
                $resource,
                EmployeeStaffingContractByPositionCategoryHandler::X,
                EmployeeStaffingContractByPositionCategoryHandler::Y,
                $options
            );

            $snapshot = $this->reportSnapshotFactory->create($report, $options);

            $logger->info(\sprintf('Snapshot created for %s', $iri ?? 'All'));
            $this->entityManager->persist($snapshot);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
