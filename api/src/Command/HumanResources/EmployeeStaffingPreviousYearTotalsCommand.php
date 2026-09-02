<?php

declare(strict_types=1);

namespace App\Command\HumanResources;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionClassification;
use App\Entity\Report\ReportSnapshot;
use App\Report\Handler\HumanResources\EmployeeStaffingContractByPositionCategoryHandler;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Report\ReportSnapshotRepository;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:employee-staffing:end-of-year')]
class EmployeeStaffingPreviousYearTotalsCommand extends Command
{
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);

        $positionClassificationRepository = $this->entityManager->getRepository(PositionClassification::class);

        /** @var BusinessUnit[] $businessUnits */
        $businessUnits = $this->entityManager->getRepository(BusinessUnit::class)->findAll();

        $resource = $this->iriConverter->getIriFromResource(People::class, UrlGeneratorInterface::ABS_PATH, new GetCollection());

        /** @var ReportSnapshotRepository $reportSnapshotRepository */
        $reportSnapshotRepository = $this->entityManager->getRepository(ReportSnapshot::class);
        foreach ($businessUnits as $businessUnit) {
            $logger->info(\sprintf('Processing snapshot value for %s', $businessUnit->getName()));
            $lastSnapshot = $reportSnapshotRepository->findSnapshot(
                $resource,
                EmployeeStaffingContractByPositionCategoryHandler::X,
                EmployeeStaffingContractByPositionCategoryHandler::Y,
                ['entity' => $this->iriConverter->getIriFromResource($businessUnit)],
                new \DateTime(Chronos::now()->toDateString())
            );

            if (!$lastSnapshot instanceof ReportSnapshot) {
                $logger->error(\sprintf('No snapshot found for %s', $businessUnit->getName()));
                continue;
            }

            $iris = array_flip($lastSnapshot->metadata[ReportHandlerInterface::METADATA_IRIS_X_KEY] ?? []);

            /** @var PositionClassification[] $classifications */
            $classifications = $positionClassificationRepository->findBy(['businessUnit' => $businessUnit]);

            foreach ($classifications as $classification) {
                $logger->debug(\sprintf('Calculating total for %s', $classification->positionCategory->name));
                $iri = $this->iriConverter->getIriFromResource($classification->positionCategory);
                if (false === ($total = ($lastSnapshot->xTotals[$iris[$iri] ?? ''] ?? false))) {
                    $classification->previousYearCorrectedTotal = 0;
                    $this->entityManager->persist($classification);
                    $logger->error(\sprintf('No value found for %s, setting zero', $classification->positionCategory->name));
                    continue;
                }
                $total += $classification->correction;
                $classification->previousYearCorrectedTotal = $total;
                $logger->debug(\sprintf('New value set for %s: %d', $classification->positionCategory->name, $total));
                $this->entityManager->persist($classification);
            }

            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
