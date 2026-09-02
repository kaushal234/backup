<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;
use LegacyBundle\Model\Task;

class TaskCcManager
{
    private readonly Connection $legacyConnection;
    private readonly TaskCommentsManager $taskCommentsManager;

    public function __construct(Connection $legacyConnection, TaskCommentsManager $taskCommentsManager)
    {
        $this->legacyConnection = $legacyConnection;
        $this->taskCommentsManager = $taskCommentsManager;
    }

    public function addCc(Task $task)
    {
        $cc = [];
        foreach ($task->getCc() as $people) {
            $qb = $this->legacyConnection->createQueryBuilder();

            $qb
                ->insert('mod_lists')
                ->setValue('parent_id', ':task_id')
                ->setValue('module', ':module')
                ->setValue('list_name', ':name')
                ->setValue('value', ':user_id')
                ->setParameter('task_id', $task->getId())
                ->setParameter('user_id', $people->getLegacyId())
                ->setParameter('name', 'task.cc')
                ->setParameter('module', 'TASK')
            ;

            $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

            $cc[] = $people->getEmail();
        }

        if ([] !== $cc) {
            $comment = \sprintf("\ncc: %s", implode(', ', $cc));
            $this->taskCommentsManager->insertComment($task, $comment);
        }
    }
}
