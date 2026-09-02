<?php

declare(strict_types=1);

namespace App\Command\Task;

use App\Entity\Directory\People;
use App\Notifier\Tasks\ScheduledTaskNotifier;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\ScheduledTaskManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tld:scheduled-task:expiration-notification',
    description: 'Send an email to the assignor when a scheduled task is about to expire.'
)]
class ScheduledTaskExpirationNotificationCommand extends Command
{
    private const EXPIRATION_NOTIFICATION_OFFSET = '+1 month +1 day';

    public function __construct(
        private readonly ScheduledTaskManager $scheduledTaskManager,
        private readonly ScheduledTaskNotifier $scheduledTaskNotifier,
        private readonly PeopleRepository $peopleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $endDate = (new \DateTime())->modify(self::EXPIRATION_NOTIFICATION_OFFSET);
        $scheduledTasks = $this->scheduledTaskManager->findActiveScheduledTasksByEndDate($endDate);
        foreach ($scheduledTasks as $scheduledTask) {
            $people = $this->peopleRepository->findOneBy(['legacyId' => $scheduledTask['assignor'], 'disabled' => false]);
            if ($people instanceof People) {
                $this->scheduledTaskNotifier->sendExpirationEmail($scheduledTask, $people);
            }
        }

        return Command::SUCCESS;
    }
}
