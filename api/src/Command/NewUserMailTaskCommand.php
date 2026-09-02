<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Directory\People;
use App\Notifier\Tasks\LegacyTaskNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:user:new:task:mail')]
class NewUserMailTaskCommand extends Command
{
    private const string TASK_IDENTIFIER = 'Welcome to Alvest Group !%';

    public function __construct(
        private readonly TaskManager $taskManager,
        private readonly LegacyTaskNotifier $legacyTaskNotifier,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Generate mail for welcome task to new people between 7th of July to the 20th of Nov 2025');
    }

    protected function configure(): void
    {
        $this->addOption(
            'date',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Date to search for tasks (format: Y-m-d)',
            date('Y-m-d')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateString = $input->getOption('date');

        try {
            $searchDate = new \DateTime($dateString);
            $searchDate->setTime(0, 0, 0);
        } catch (\Exception $e) {
            $output->writeln('<error>Invalid date format. Use Y-m-d format.</error>');

            return Command::FAILURE;
        }

        $output->writeln(\sprintf(
            '<info>Searching for welcome tasks created on %s...</info>',
            $searchDate->format('Y-m-d')
        ));

        // Retrieve welcome tasks created on the specified date
        $tasks = $this->getWelcomeTasksCreatedOnDate($searchDate);

        if (empty($tasks)) {
            $output->writeln('<comment>No welcome tasks found for this date.</comment>');

            return Command::SUCCESS;
        }

        $output->writeln(\sprintf(
            '<info>Found %d welcome task(s). Sending emails...</info>',
            \count($tasks)
        ));

        $successCount = 0;

        /* @var People $peopleAssignee */
        foreach ($tasks as $task) {
            try {
                $peopleRepository = $this->entityManager->getRepository(People::class);
                $peopleAssignee = $peopleRepository->findOneBy(['legacyId' => $task['assignee']]);

                $taskMail = (new Task())
                    ->setId($task['id'])
                    ->setAssignee($peopleAssignee)
                    ->setAssignor($peopleAssignee->getSupervisor())
                    ->setModule('USER')
                    ->setLocation($peopleAssignee->getBusinessUnit()->getLocation())
                    ->setDescription($task['task'])
                    ->setParentId($peopleAssignee->getLegacyId())
                ;

                if (!$peopleAssignee->getEmail()) {
                    $output->writeln(\sprintf(
                        '<error>Task #%s: Assignee has no valid email address - skipped</error>',
                        $task['id']
                    ));
                    continue;
                }

                $this->legacyTaskNotifier->sendEmail($taskMail);

                $output->writeln(\sprintf(
                    '<info>Email sent to (%s)</info>',
                    $peopleAssignee->getEmail()
                ));

                ++$successCount;
            } catch (\Exception $e) {
                $output->writeln(\sprintf(
                    '<error>Failed to send email for task #%s: %s</error>',
                    $task['id'],
                    $e->getMessage()
                ));
            }
        }

        $output->writeln('');
        $output->writeln(\sprintf(
            '<info>Summary: %d email(s) sent successfully</info>',
            $successCount,
        ));

        return Command::SUCCESS;
    }

    /**
     * @return Task[]
     */
    private function getWelcomeTasksCreatedOnDate(\DateTime $date): array
    {
        $startOfDay = clone $date;
        $startOfDay->setTime(0, 0, 0);

        $endOfDay = clone $date;
        $endOfDay->setTime(23, 59, 59);

        return $this->taskManager->findTasksByDescriptionAndDateRange(
            'USER',
            self::TASK_IDENTIFIER,
            $startOfDay,
            $endOfDay
        );
    }
}
