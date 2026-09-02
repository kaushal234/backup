<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'forms_and_reports.inc.php';
include_once 'calendar.inc.php';
include_once 'user.inc.php';
include_once 'erp.inc.php';

#[AsCommand(name: 'service:pdc:notify-late-pending')]
class NotifyCcoForLatePendingPdcCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Notify the CCO if a PDC stays PENDING more then 72h';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = <<<'SQL'
SELECT
    id,
    factory
FROM demerit
WHERE status = 'PENDING'
AND DATEDIFF(NOW(), status_updated_at)>3
SQL;

        $rows = \tldUtils::getSqlToAssocArray($query);

        $pdcByFactory = [];
        foreach ($rows as $row) {
            $pdcByFactory[$row['factory']][] = $row['id'];
        }

        $module = 'PDC';
        foreach ($pdcByFactory as $key => $factory) {
            $coo = new \tldGroup('ROLE_COO', \tldLocation::getERPByID($key));
            $usersCOO = $coo->getUserlist();
            foreach ($usersCOO as $userCOO) {
                $taskDesc = 'You have PDC in PENDING for more than three days, please ensure your team takes the necessary steps to move this PDC to IN PROGRESS <br>Thanks<br>List of PDC:';
                foreach ($factory as $pdc) {
                    $taskDesc .= "<br><a href=\"http://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id={$pdc}\">click here to see the PDC # {$pdc}</a>";
                }
                $vals = [
                    'module' => $module,
                    'assignee' => $userCOO['id'],
                    'assignor' => $userCOO['id'],
                    'task' => $taskDesc,
                    'bu_id' => $key,
                    'escalation_trigger' => '30',
                    'due_date' => ['value' => '0', 'unit' => 'DAY'],
                ];

                $task = \tldUtils::cleanupFormInput($vals);
                // insert new task
                $errorTask = \tldTask::insert('', $task, $module);
                if (!is_numeric($errorTask)) {
                    $this->logger->error("Could not create new task. There was an error processing. The error returned is '$errorTask'");
                } else {
                    $task = new \tldTask($errorTask);
                    // built the email notification for the newly created task
                    $assignee = new \tldUser($task->getAssignee());
                    $assigneeFullname = $assignee->getFullname();
                    $message = <<<EOF
			Task #$errorTask has been assigned to $assigneeFullname.\n<br>
			Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
			<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$errorTask">

			Click here to go to Task.</a>
		<br>
		Task:<br>
EOF;
                    $subject = "Tasks, New: #$errorTask opened for ".$assignee->getFullname();
                    $task->notifyAssignee($message, $subject);
                    $this->logger->info("Task $errorTask assigned to $assigneeFullname");
                }
            }
        }

        return Command::SUCCESS;
    }
}
