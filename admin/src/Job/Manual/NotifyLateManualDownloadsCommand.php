<?php

declare(strict_types=1);

namespace App\Job\Manual;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'publications.inc.php';

#[AsCommand(name: 'manual:late')]
class NotifyLateManualDownloadsCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Notify Late Manual Downloads,';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \manual::emailNotifyLateDownloads();

        return Command::SUCCESS;
    }
}
