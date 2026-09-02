<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\Position;
use App\Filter\RelationDiscrFilter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RelationDiscrFilterTest extends KernelTestCase
{
    private ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->managerRegistry = $managerRegistry;
    }

    public function testQueryBuilderHasAnExistsWhereWithValidDiscriminators(): void
    {
        $filter = new RelationDiscrFilter(
            $this->managerRegistry,
            properties: ['discriminator' => 'users']
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $exprMock = $this->createMock(Expr::class);
        $existsFunc = new Expr\Func('EXISTS', ['SELECT 1 FROM ...']);

        $queryBuilderMock->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['o']);

        $queryBuilderMock->expects($this->once())
            ->method('expr')
            ->willReturn($exprMock);

        $exprMock->expects($this->once())
            ->method('exists')
            ->willReturn($existsFunc);

        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with($existsFunc);

        $filter->apply(
            $queryBuilderMock,
            new QueryNameGenerator(),
            Position::class,
            null,
            ['filters' => ['discriminator' => ['people', 'extranet_user', 'vendor_user']]]
        );
    }

    public function testQueryBuilderIsNotUpdatedWhenNoFilter(): void
    {
        $filter = new RelationDiscrFilter(
            $this->managerRegistry,
            properties: ['discriminator' => 'users']
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->never())
            ->method('getRootAliases');

        $queryBuilderMock->expects($this->never())
            ->method('andWhere');

        $filter->apply(
            $queryBuilderMock,
            new QueryNameGenerator(),
            Position::class,
            null,
            ['filters' => []]
        );
    }

    public function testQueryBuilderIsNotUpdatedWhenInvalidDiscriminators(): void
    {
        $filter = new RelationDiscrFilter(
            $this->managerRegistry,
            properties: ['discriminator' => 'users']
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['o']);

        $queryBuilderMock->expects($this->never())
            ->method('andWhere');

        $filter->apply(
            $queryBuilderMock,
            new QueryNameGenerator(),
            Position::class,
            null,
            ['filters' => ['discriminator' => ['invalid_type', 'another_invalid_type']]]
        );
    }

    public function testFallbackToParentWhenNoRelationPropertyDefined(): void
    {
        $filter = new RelationDiscrFilter(
            $this->managerRegistry,
            properties: []
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->never())
            ->method('andWhere');

        $filter->apply(
            $queryBuilderMock,
            new QueryNameGenerator(),
            Position::class,
            null,
            ['filters' => ['discriminator' => ['people']]]
        );
    }

    public function testGetDescription(): void
    {
        $filter = new RelationDiscrFilter(
            $this->managerRegistry,
            properties: ['discriminator' => 'users']
        );

        $description = $filter->getDescription(Position::class);

        self::assertArrayHasKey('discriminator[]', $description);
        self::assertSame('discriminator', $description['discriminator[]']['property']);
        self::assertSame('string', $description['discriminator[]']['type']);
        self::assertFalse($description['discriminator[]']['required']);
        self::assertTrue($description['discriminator[]']['is_collection']);
    }
}
