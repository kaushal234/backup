<?php

declare(strict_types=1);

namespace App\Job\Calendar;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'calendar.inc.php';

#[AsCommand(name: 'calendar:tickets:clean')]
class TicketatorCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Clean backlog of tickets';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modules = [];
        foreach (\tldModule::getList() as $module) {
            $modules[$module['id']] = $module;
        }

        $tickets = \tldTask::byConstraints("T1.module = 'TTS' AND T1.status != 'CLOSED' AND T1.parent_id=1653");
        foreach ($tickets as $ticket) {
            $task = new \tldTask($ticket['id']);
            $module = $modules[$ticket['ticket_module_id']] ?? null;
            if (null !== $module && 'C' !== $ticket['cat'] && \in_array($module['module'], ['SPQ', 'AR', 'PO', 'RFQ'], true)) {
                $message = \sprintf('Closing ticket %s because the module %s is abandoned', $ticket['id'], $module['module']);
                $this->logger->info($message);
                $task->addComment($message);
                $task->close();
            }

            if ((int) $ticket['assignee'] !== (int) $module['oid'] && null !== $module && \in_array((int) $ticket['assignee'], [
                2853, // Philippe
                7070, // Ariane
                49, // Jean-Paul
                8358, // Alice
            ], true)) {
                $message = \sprintf('Transferring ticket %s back to MIS owner %s', $ticket['id'], $module['uid_fullname']);
                $this->logger->info($message);
                $task->addComment($message);
                $task->transfer($module['uid']);
            }

            switch ($ticket['cat']) {
                case 'C':
                    $message = \sprintf('Rescheduling ticket %s to 2023-01-15', $ticket['id']);
                    $this->logger->info($message);
                    $task->addComment($message);
                    $task->reschedule('2023-01-15');
                    break;
                case 'B':
                    if (null !== $module) {
                        $message = \sprintf('Transferring ticket B %s back to MOO %s', $ticket['id'], $module['oid_fullname']);
                        $this->logger->info($message);
                        $task->addComment($message);
                        $task->transfer($module['oid']);
                    }
                    $message = \sprintf('Pausing ticket B %s', $ticket['id']);
                    $this->logger->info($message);
                    $task->addComment($message);
                    $task->pause();
                    break;
            }
        }

        return Command::SUCCESS;
    }
}
