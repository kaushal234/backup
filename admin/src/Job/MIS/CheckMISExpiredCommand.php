<?php

declare(strict_types=1);

namespace App\Job\MIS;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'mis.inc.php';

#[AsCommand(name: 'mis:inventory')]
class CheckMISExpiredCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Check MIS inventory expired items, expired contracts, expired warranties and purge material list';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('MIS Inventory check expired');
        \Tld_Mis_Inventory_Item::checkExpired();

        $this->logger->info('MIS Inventory contract expired');
        \Tld_Mis_Inventory_Item::checkContractExpired();
        $this->logger->info('MIS Inventory warranty expired');
        \Tld_Mis_Inventory_Item::checkWarrantyExpired();

        $this->logger->info('MIS purge Material list from tld..temp_bom');
        \tldUtils::sqlExecute('EXEC cleanTempBOM;', 'odbc', ['src' => 'baan']);

        return Command::SUCCESS;
    }
}
