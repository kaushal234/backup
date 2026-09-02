<?php

declare(strict_types=1);

namespace App\Entity\Task;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Get(),
    ]
)]
#[ORM\Entity]
#[App\Loggable(owner: 'task', ownerRelation: 'files')]
class TaskFile extends File
{
    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'files')]
    private Task $task;

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): void
    {
        $this->task = $task;
    }
}
