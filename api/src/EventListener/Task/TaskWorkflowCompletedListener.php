<?php

declare(strict_types=1);

namespace App\EventListener\Task;

use App\Entity\Task\Task;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

readonly class TaskWorkflowCompletedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.task.completed.close_to_pending' => ['completedToReopenTask'],
            'workflow.task.completed.in_progress_to_close' => ['completedToCloseTask'],
            'workflow.task.completed.pending_to_close' => ['completedToCloseTask'],
        ];
    }

    public function completedToReopenTask(CompletedEvent $event): void
    {
        $task = $event->getSubject();
        if (!$task instanceof Task) {
            return;
        }

        $task->closedAt = null;
    }

    public function completedToCloseTask(CompletedEvent $event): void
    {
        $task = $event->getSubject();
        if (!$task instanceof Task) {
            return;
        }

        $task->closedAt = new \DateTime();
    }
}
