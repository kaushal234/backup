<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Factory;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Factory\TaskFactory;
use LegacyBundle\Model\Task;
use PHPUnit\Framework\TestCase;

class TaskFactoryTest extends TestCase
{
    public function testCreate()
    {
        $taskFactory = new TaskFactory();
        $task = $taskFactory->create(
            module: 'TEST',
            assignee: new People(),
            assignor: new People(),
            description: 'desc',
            location: new Location(),
            dueDate: new \DateTime(),
            cc: new ArrayCollection(),
            legacyReferenceId: 1
        );

        self::assertInstanceOf(Task::class, $task);
    }
}
