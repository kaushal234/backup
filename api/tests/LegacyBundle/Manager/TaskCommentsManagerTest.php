<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Manager;

use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use LegacyBundle\Manager\TaskCommentsManager;
use LegacyBundle\Model\Task;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskCommentsManagerTest extends TestCase
{
    public function testLegacyInsertQueryIsOKWithValidTask()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        $expectedSQL = <<<'SQL'
            INSERT INTO tasks_comments (parent_id, comment, date, poster) VALUES(:parent, :comment, NOW(), :poster)
            SQL;

        $expectedParameters = [
            'parent' => 69,
            'comment' => "Hell&ocirc;&ocirc;&ocirc;, is it me you're looking for?",
            'poster' => 54,
        ];

        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with($expectedSQL, $expectedParameters);

        /** @var TaskCommentsManager $manager */
        $manager = new TaskCommentsManager($connection);

        $task = (new Task())->setId(69)->setAssignor((new People())->setLegacyId(54));

        $manager->insertComment($task, "Hellôôô, is it me you're looking for?");
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
