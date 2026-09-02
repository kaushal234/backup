<?php

declare(strict_types=1);

namespace App\Job\MIS;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mis:database:cleanup')]
class DatabaseCleanup extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Cleanup of data from tables stats_access_log, pi_timekeeping_log, erp_licences and erp_archive';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Cleaning access logs (table stats_access_log)');
        $limit = (new \DateTimeImmutable('midnight first day of this month'))->modify('-1 year');
        $query = <<<SQL
    DELETE FROM stats_access_log WHERE dt < '{$limit->format('Y-m-d h:i:s')}'
SQL;
        \TldDatabase::query($query);

        $this->logger->info('Cleaning PIO timekeeping logs (table pi_timekeeping_log)');
        $limit = (new \DateTimeImmutable('midnight first day of this month'))->modify('-3 months');
        $query = <<<SQL
    DELETE FROM pi_timekeeping_log WHERE created_on < '{$limit->format('Y-m-d h:i:s')}'
SQL;
        \TldDatabase::query($query);

        $this->logger->info('Cleaning ERP licences monitoring (table erp_licences)');
        $limit = (new \DateTimeImmutable('midnight first day of this month'))->modify('-2 years');
        $query = <<<SQL
    DELETE FROM erp_licences WHERE dt < '{$limit->format('Y-m-d h:i:s')}'
SQL;
        \TldDatabase::query($query);

        $this->logger->info('Cleaning ERP archive (table erp_archive)');
        $limit = (new \DateTimeImmutable('midnight first day of this month'))->modify('-1 year');
        $query = <<<SQL
    DELETE FROM erp_archive WHERE dt < '{$limit->format('Y-m-d h:i:s')}'
SQL;
        \TldDatabase::query($query);

        return Command::SUCCESS;
    }
}
