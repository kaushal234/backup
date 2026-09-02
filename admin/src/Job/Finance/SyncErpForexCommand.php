<?php

declare(strict_types=1);

namespace App\Job\Finance;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'common.inc.php';

#[AsCommand(name: 'finance:forex')]
class SyncErpForexCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Push forex data to equotes';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = 'SELECT * FROM erp_forex2';
        $foreignExchangeRates = \tldUtils::getSqlToAssocArray($query);

        \tldUtils::connectDb('equotes');
        \tldUtils::sqlQuery('DELETE FROM erp_forex2', null, ['src' => 'equotes']);

        foreach ($foreignExchangeRates as $foreignExchangeRate) {
            $foreignExchangeRate = \tldUtils::cleanupFormInput($foreignExchangeRate);
            $query = "INSERT INTO erp_forex2
		        SET
                    parent_id='{$foreignExchangeRate['parent_id']}',
                    dt='{$foreignExchangeRate['dt']}',
                    nam_year='{$foreignExchangeRate['nam_year']}',
                    nam_month='{$foreignExchangeRate['nam_month']}',
                    nam_cur='{$foreignExchangeRate['nam_cur']}',
                    typ='{$foreignExchangeRate['typ']}',
                    rate='{$foreignExchangeRate['rate']}'";
            $error = \tldUtils::sqlQuery($query, null, ['src' => 'equotes']);

            if ($error) {
                $this->logger->error($error);
            }
        }

        return Command::SUCCESS;
    }
}
