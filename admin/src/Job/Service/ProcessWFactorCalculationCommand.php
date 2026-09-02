<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'sales_service.inc.php';

#[AsCommand(name: 'service:toc')]
class ProcessWFactorCalculationCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Process TOC W factor calculation';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \tldTOC::processWFactorCalculation();

        return Command::SUCCESS;
    }
}
