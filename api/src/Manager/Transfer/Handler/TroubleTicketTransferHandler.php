<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\BaseTask;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\Task\Task;

class TroubleTicketTransferHandler extends AbstractTransferHandler
{
    public function handle($source, $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (!$this->supports($relation->getReflectionClass()->getName(), $relation->getReflectionProperty())) {
            return;
        }

        $qb = $this->getQueryBuilder($source, $relation->getReflectionClass()->getName(), $relation->getReflectionProperty()->getName());

        $statuses = array_merge(
            TroubleTicket::OPEN_STATUSES,
            [BaseTask::IN_PROGRESS, BaseTask::PENDING]
        );

        $qb
            ->andWhere('o.status IN (:openStatuses)')
            ->setParameter('openStatuses', $statuses);

        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    public function getName(): string
    {
        return 'handler.base_task';
    }

    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return \in_array($className, [TroubleTicket::class, Task::class], true) && \in_array($property->getName(), ['assignee', 'misAssignee', 'createdBy'], true);
    }
}
