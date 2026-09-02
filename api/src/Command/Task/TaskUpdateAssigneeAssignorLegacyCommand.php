<?php

declare(strict_types=1);

namespace App\Command\Task;

use App\Entity\Task\Task;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'update:task_legacy')]
class TaskUpdateAssigneeAssignorLegacyCommand extends Command
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public Connection $legacyConnection,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->entityManager->getRepository(Task::class)->findAll() as $task) {
            $this->legacyConnection->executeQuery(
                \sprintf('UPDATE tasks SET assignee = %d, assignor = %d WHERE id= %d', $task->assignee?->getLegacyId(), $task->createdBy?->getLegacyId(), $task->getLegacyId())
            );
        }

        return Command::SUCCESS;
    }
}
