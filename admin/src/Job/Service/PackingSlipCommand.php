<?php

declare(strict_types=1);

namespace App\Job\Service;

include_once 'erp.inc.php';

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'service:packing_slip')]
class PackingSlipCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Insert packing slips';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \tldSO::insertPackingSlip();

        return Command::SUCCESS;
    }
}
