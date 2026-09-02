<?php

declare(strict_types=1);

namespace App\Command\MIS;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\Handler\MIS\TroubleTicketByStatusHandler;
use App\Report\ReportGenerator;
use App\Report\ReportSnapshotFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:trouble-ticket:snapshot')]
class TroubleTicketSnapshotCommand extends Command
{
    public function __construct(
        private readonly ReportGenerator $reportGenerator,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly ReportSnapshotFactory $reportSnapshotFactory
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resource = $this->iriConverter->getIriFromResource(TroubleTicket::class, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        $report = $this->reportGenerator->getReport(
            $resource,
            TroubleTicketByStatusHandler::X,
            TroubleTicketByStatusHandler::Y,
        );

        $snapshot = $this->reportSnapshotFactory->create($report, []);
        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
