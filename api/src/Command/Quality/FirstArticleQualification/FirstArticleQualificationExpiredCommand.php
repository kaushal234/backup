<?php

declare(strict_types=1);

namespace App\Command\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Notifier\Tasks\LegacyTaskNotifier;
use App\Repository\Quality\FirstArticleQualification\FirstArticleQualificationRepository;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:faq:expired')]
class FirstArticleQualificationExpiredCommand extends Command
{
    private readonly FirstArticleQualificationRepository $firstArticleQualificationRepository;
    private readonly TaskManager $taskManager;
    private readonly LegacyTaskNotifier $taskNotifier;

    public function __construct(FirstArticleQualificationRepository $firstArticleQualificationRepository, TaskManager $taskManager, LegacyTaskNotifier $taskNotifier)
    {
        parent::__construct();
        $this->setDescription('Generate a task if FAQ due date is passed');
        $this->firstArticleQualificationRepository = $firstArticleQualificationRepository;
        $this->taskManager = $taskManager;
        $this->taskNotifier = $taskNotifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var FirstArticleQualification[] $firstArticleQualifications */
        $firstArticleQualifications = $this->firstArticleQualificationRepository->findFAQDueDatePassed();

        foreach ($firstArticleQualifications as $firstArticleQualification) {
            $task = (new Task())
                ->setModule('FAQ')
                ->setLocation($firstArticleQualification->getLocation())
                ->setParentId($firstArticleQualification->getId())
                ->setAssignee($firstArticleQualification->getPoster())
                ->setAssignor($firstArticleQualification->getOwner())
                ->setEscalationTrigger(10)
                ->setDueDate(new \DateTime('+10 days'))
                ->setDescription(\sprintf('FAQ %s is past due. Please take action to qualify the part or modify the FAQ due date', $firstArticleQualification->getId()))
            ;

            try {
                $this->taskManager->insert($task);
            } catch (\Exception $exception) {
                $output->writeln('Task not created. Reason: '.$exception->getMessage());
            }

            $this->taskNotifier->sendEmail($task);
        }

        return Command::SUCCESS;
    }
}
