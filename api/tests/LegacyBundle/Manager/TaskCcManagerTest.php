<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Manager;

use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use LegacyBundle\Manager\TaskCcManager;
use LegacyBundle\Manager\TaskCommentsManager;
use LegacyBundle\Model\Task;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TaskCcManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testLegacyInsertQueryForAddCc()
    {
        $user1 = new People();
        $user1->setLegacyId(54)->setEmail('jean-bon@tld.fr');
        $user2 = new People();
        $user2->setLegacyId(55)->setEmail('jean-peupu@tld.fr');
        $assignor = (new People())->setLegacyId(22);
        $task = (new Task())->setId(72);
        $task
            ->addCc($user1)
            ->addCc($user2)
        ;
        $task->setAssignor($assignor);

        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();
        $taskCommentsManager = $this->prophesize(TaskCommentsManager::class);
        $taskCommentsManager->insertComment($task, "\ncc: jean-bon@tld.fr, jean-peupu@tld.fr")->shouldBeCalledTimes(1);

        $expectedSQL = <<<'SQL'
            INSERT INTO mod_lists (parent_id, module, list_name, value) VALUES(:task_id, :module, :name, :user_id)
            SQL;

        $connection
            ->expects(self::exactly(2))
            ->method('executeQuery')
            ->withConsecutive(
                [$expectedSQL, [
                    'module' => 'TASK',
                    'task_id' => $task->getID(),
                    'name' => 'task.cc',
                    'user_id' => $user1->getLegacyId(),
                ]],
                [$expectedSQL, [
                    'module' => 'TASK',
                    'task_id' => $task->getID(),
                    'name' => 'task.cc',
                    'user_id' => $user2->getLegacyId(),
                ]]
            );

        $manager = new TaskCcManager($connection, $taskCommentsManager->reveal());
        $manager->addCc($task);
    }

    private function getConnection(): MockObject
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $connection
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connection));

        return $connection;
    }
}
