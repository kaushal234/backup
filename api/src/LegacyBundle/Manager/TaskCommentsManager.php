<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Model\Task;

class TaskCommentsManager
{
    private readonly Connection $legacyConnection;

    /**
     * TaskCommentsManager constructor.
     */
    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function insertComment(Task $task, string $comment, ?People $poster = null)
    {
        $transform = new Utf8ToHtmlEntities();

        $qb = $this->legacyConnection->createQueryBuilder();

        $qb
            ->insert('tasks_comments')
            ->setValue('parent_id', ':parent')
            ->setValue('comment', ':comment')
            ->setValue('date', 'NOW()')
            ->setValue('poster', ':poster')
            ->setParameters([
                'parent' => $task->getId(),
                'comment' => $transform($comment, []),
                'poster' => null !== $poster ? $poster->getLegacyId() : $task->getAssignor()->getLegacyId(),
            ])
        ;

        $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());
    }
}
