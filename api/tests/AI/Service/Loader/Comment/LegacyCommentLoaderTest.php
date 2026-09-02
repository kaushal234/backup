<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Loader\Comment;

use App\AI\Service\Loader\Comment\LegacyCommentLoader;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcess;
use PHPUnit\Framework\TestCase;

final class LegacyCommentLoaderTest extends TestCase
{
    public function testMapsRowsAndSkipsBlank(): void
    {
        $entity = $this->createMock(EngineeringActivityProcess::class);
        $entity->method('getId')->willReturn(123);

        $rows = [
            ['comment' => '  Real comment  ', 'date' => '2025-01-02 10:00:00', 'poster' => 7],
            ['comment' => '   ', 'date' => '2025-01-03 10:00:00', 'poster' => 7],
            ['comment' => 'Anonymous', 'date' => '2025-01-04 10:00:00', 'poster' => null],
        ];

        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($rows);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('setParameters')->willReturnSelf();
        $qb->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')->willReturn($qb);

        $author = $this->createMock(People::class);
        $author->method('getLegacyId')->willReturn(7);
        $author->method('getEmail')->willReturn('john@example.com');
        $author->method('getFirstname')->willReturn('John');
        $author->method('getLastname')->willReturn('Doe');

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $peopleRepo->method('findBy')->with(['legacyId' => [7]])->willReturn([$author]);

        $loader = new LegacyCommentLoader($connection, $peopleRepo);
        $comments = $loader->findComments($entity, 'EAP');

        self::assertCount(2, $comments);
        self::assertSame('Real comment', $comments[0]->message);
        self::assertSame('john@example.com', $comments[0]->authorEmail);
        self::assertSame('Anonymous', $comments[1]->message);
        self::assertNull($comments[1]->authorEmail);
    }
}
