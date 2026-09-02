<?php

declare(strict_types=1);

namespace App\Command;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\DMS;
use App\Report\ReportGenerator;
use App\Report\ReportSnapshotFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:dms:kpi-snapshot')]
class DMSReportSnapshotCommand extends Command
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
        $resource = $this->iriConverter->getIriFromResource(DMS::class, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        $options = ['type' => 'Sales Material'];

        $report = $this->reportGenerator->getReport(
            $resource,
            'owner.businessUnit.name',
            'status',
            $options
        );

        $snapshot = $this->reportSnapshotFactory->create($report, $options);
        $this->entityManager->persist($snapshot);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
