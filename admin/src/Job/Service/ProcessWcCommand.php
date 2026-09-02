<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'product_support.inc.php';

#[AsCommand(name: 'service:wc')]
class ProcessWcCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Process conditionnal WC, then process dispute WC';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \tldWC::emailCondPast2Months();
        \tldWC::emailCondPast6Weeks();

        \tldWC::emailDisputePast2Months();
        \tldWC::emailDisputePast6Weeks();

        return Command::SUCCESS;
    }
}
