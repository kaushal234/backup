<?php

declare(strict_types=1);

namespace LegacyBundle\Factory;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\Collection;
use LegacyBundle\Model\Task;

class TaskFactory
{
    public function create(
        string $module,
        People $assignee,
        People $assignor,
        string $description,
        Location $location,
        \DateTime $dueDate,
        Collection $cc,
        int $legacyReferenceId,
    ): Task {
        $task = new Task();

        $task
            ->setParentId($legacyReferenceId)
            ->setModule($module)
            ->setAssignee($assignee)
            ->setAssignor($assignor)
            ->setDescription($description)
            ->setLocation($location)
            ->setDate(new \DateTime())
            ->setDueDate($dueDate)
        ;

        foreach ($cc as $person) {
            $task->addCc($person);
        }

        return $task;
    }
}
