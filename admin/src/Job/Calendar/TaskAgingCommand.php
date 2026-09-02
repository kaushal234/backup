<?php

declare(strict_types=1);

namespace App\Job\Calendar;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'forms_and_reports.inc.php';
include_once 'calendar.inc.php';

#[AsCommand(name: 'calendar:task:aging')]
class TaskAgingCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Send reports on old tasks to managers';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $WEB_ROOT;
        $_CSS = file_get_contents("$WEB_ROOT/tld-gse.css");

        $user_rows = \tldUtils::getSqlToAssocArray("SELECT *, concat(lastname, ', ', firstname) as fullname FROM people WHERE hidden<>1 AND disabled='N' AND email<>''");

        foreach ($user_rows as $user_row) {
            $message_body = '';
            $count = 0;

            $u = new \tldUser($user_row['id']);
            $users = $u->getSubordinates();
            array_unshift($users, $user_row);

            foreach ($users as $user) {
                $rows = \tldTask::countByWeeksOverdue($user['id']);
                $count += \count($rows);
                $report = new \tldReportColumnar(
                    $rows,
                    ['xItems' => ['wk' => 'Weeks Late', 'cnt' => 'Count'],
                        'title' => 'Number of tasks against Weeks late for '.$user['fullname'],
                        'links' => ['wk' => 'http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byWeeksLate&assignee='.$user['id'].'&wk='],
                    ]
                );
                $message_body .= $report->fetch();
            }

            if (0 === $count) {
                $this->logger->debug('Nothing to send for '.$user_row['email']);
                continue;
            }

            // Sends Email
            \tldUtils::emailAttachment(
                $user_row['email'],
                'noreply@tld-gse.com',
                'Weekly Task aging report ',
                "<html><head><style type=\"text/css\">$_CSS</style></head><body>$message_body</body></html>"
            );

            $this->logger->info('Email sent to: '.$user_row['email']);
        }

        return Command::SUCCESS;
    }
}
