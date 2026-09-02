<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Task\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskReopenController extends AbstractController
{
    public function __invoke(Task $task): Task
    {
        $task->setStatus(Task::PENDING);
        $task->closedAt = null;

        return $task;
    }
}
