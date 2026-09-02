<?php

declare(strict_types=1);

namespace App\Job\Calendar;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'calendar.inc.php';

#[AsCommand(name: 'calendar:task:escalation')]
class AutoEscalationCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Auto Escalate tasks';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \tldTask::emailNotifyDelinquent();

        return Command::SUCCESS;
    }
}
