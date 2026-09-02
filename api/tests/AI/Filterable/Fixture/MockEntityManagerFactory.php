<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Builds a minimal EntityManagerInterface mock chain so that
 *   $em->getRepository($class)->createQueryBuilder($alias)
 * returns a QueryBuilder mock whose ->getQuery()->getResult() yields the canned entities.
 *
 * Captured: the limit passed to QueryBuilder::setMaxResults() and whether ->distinct()
 * was called.
 *
 * Intended for use from PHPUnit TestCase subclasses (createMock is protected).
 */
trait MockEntityManagerFactory
{
    public ?int $capturedLimit = null;
    public bool $distinctCalled = false;

    /**
     * @param object[] $cannedResult
     */
    private function buildMockEntityManager(array $cannedResult): EntityManagerInterface
    {
        \assert($this instanceof TestCase);

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();
        $query->method('getResult')->willReturn($cannedResult);

        /** @var QueryBuilder&MockObject $qb */
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('addOrderBy')->willReturnSelf();
        $qb->method('distinct')->willReturnCallback(function () use ($qb): QueryBuilder {
            $this->distinctCalled = true;

            return $qb;
        });
        $qb->method('setMaxResults')->willReturnCallback(function (?int $limit) use ($qb): QueryBuilder {
            $this->capturedLimit = $limit;

            return $qb;
        });
        $qb->method('getQuery')->willReturn($query);
        $qb->method('getRootAliases')->willReturn(['x']);

        /** @var EntityRepository<object>&MockObject $repo */
        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repo->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        return $em;
    }

    /**
     * Builds a ManagerRegistry mock whose getManagerForClass() returns
     * the EM built by {@see buildMockEntityManager}.
     *
     * @param object[] $cannedResult
     */
    private function buildMockManagerRegistry(array $cannedResult, ?EntityManagerInterface $em = null): ManagerRegistry
    {
        \assert($this instanceof TestCase);

        $em ??= $this->buildMockEntityManager($cannedResult);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return $registry;
    }
}
