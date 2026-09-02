<?php

declare(strict_types=1);

namespace App\Job\Calendar;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'calendar.inc.php';

#[AsCommand(name: 'calendar:task:scheduled')]
class ScheduledTasksCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Generate tasks from ST';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \tldST::doTasks();

        return Command::SUCCESS;
    }
}
