<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Directory\People;
use App\Entity\Task\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TaskCommentController extends AbstractController
{
    public function __invoke(Task $task, Request $request, #[CurrentUser] People $user): Task
    {
        $route = $request->attributes->get('_route');

        switch ($route) {
            case 'task_comment':
                if (Task::PENDING === $task->getStatus() && $user === $task->assignee) {
                    $task->setStatus(Task::IN_PROGRESS);
                }
                if (Task::PAUSE === $task->getStatus() && $this->isGranted('FEATURE_PAUSE_UNPAUSE_TASK')) {
                    $task->setStatus(Task::IN_PROGRESS);
                }
                break;
            case 'task_close_comment':
                $task->setStatus(Task::CLOSED);
                $task->closedAt = new \DateTime();
                $task->closeComment = $task->comment;
                break;
            case 'task_pause_comment':
                $task->setStatus(Task::PAUSE);
                break;
        }

        $task->lastComment = $task->comment;

        return $task;
    }
}
