<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\SupplierRanking\SupplierRankingFile;
use App\Filter\Purchasing\SupplierRanking\HasExpiredFilesFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HasExpiredFilesFilterTest extends TestCase
{
    public function testAppliesExistsClauseForYesValue(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(['hasExpiredFiles' => '1']));
        $filter = new HasExpiredFilesFilter($requestStack);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['sr']);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $queryBuilder->method('getEntityManager')->willReturn($entityManager);

        $subQueryBuilder = $this->createMock(QueryBuilder::class);
        $subQueryBuilder->expects($this->once())->method('select')->with('1')->willReturnSelf();
        $subQueryBuilder->expects($this->once())->method('from')->with(SupplierRankingFile::class, 'expiredFilesAlias')->willReturnSelf();
        $subQueryBuilder->expects($this->atLeastOnce())->method('where')->willReturnSelf();
        $subQueryBuilder->expects($this->atLeastOnce())->method('andWhere')->willReturnSelf();
        $subQueryBuilder->expects($this->once())->method('getDQL')->willReturn('SELECT 1');

        $entityManager->expects($this->once())->method('createQueryBuilder')->willReturn($subQueryBuilder);

        $queryNameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $queryNameGenerator->expects($this->once())->method('generateJoinAlias')->with('expired_files')->willReturn('expiredFilesAlias');

        $queryBuilder->expects($this->once())->method('setParameter')->with($this->anything(), $this->isInstanceOf(\DateTimeInterface::class))->willReturnSelf();
        $queryBuilder->expects($this->once())->method('andWhere')->with($this->stringContains('EXISTS'));

        $filter->apply($queryBuilder, $queryNameGenerator, SupplierRanking::class);
    }
}
