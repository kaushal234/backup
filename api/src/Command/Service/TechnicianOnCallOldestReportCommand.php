<?php

declare(strict_types=1);

namespace App\Command\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\OldestReport;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:technician_on_call:oldest_report')]
class TechnicianOnCallOldestReportCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'EOF'
                SELECT t.id
                FROM technician_on_call t
                JOIN directory_location l ON l.id = t.sales_organisation_service_id
                JOIN (
                    SELECT
                        sales_organisation_service_id,
                        MIN(created_at) AS min_created
                    FROM technician_on_call
                    WHERE status IN (:statuses)
                    GROUP BY sales_organisation_service_id
                ) AS sub ON t.sales_organisation_service_id = sub.sales_organisation_service_id AND t.created_at = sub.min_created
                WHERE t.status IN (:statuses)
            EOF;

        $results = $this->connection->executeQuery($sql, [
            'statuses' => TechnicianOnCall::OPENED_STATUSES,
        ], [
            'statuses' => ArrayParameterType::STRING,
        ])->fetchAllAssociative();

        $technicianOnCalls = [];
        foreach ($results as $result) {
            $technicianOnCalls[] = $this->entityManager->getReference(TechnicianOnCall::class, $result['id']);
        }

        $date = (new \DateTimeImmutable('last day of previous month'))->setTime(23, 59, 59);

        /** @var TechnicianOnCall $technicianOnCall */
        foreach ($technicianOnCalls as $technicianOnCall) {
            $report = new OldestReport();
            $report->technicianOnCall = $technicianOnCall;
            $report->salesOrganisation = $technicianOnCall->salesOrganisationService;
            $report->date = $date;
            $report->days = $date->diff($technicianOnCall->createdAt)->days;

            $this->entityManager->persist($report);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
