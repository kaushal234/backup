<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'product_support.inc.php';

#[AsCommand(name: 'service:pdc')]
class UpdatePdcCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Update focus weight history of PDC, then update Demerit In Progress in the Mod_list table used by the KPI "PDC In Progress" (only on 15th of the month),';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \tldPDC::updateFocusWeightHistory();

        if ('15' === date('d')) {
            \tldPDC::updateKPIInProgress();
        }

        return Command::SUCCESS;
    }
}
