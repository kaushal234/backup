<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\ApiPlatform\UniqueResourceMetadataCollectionFactory;
use App\Entity\User;
use App\Filter\DiscriminatorFilter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiscriminatorFilterTest extends KernelTestCase
{
    use ProphecyTrait;

    private ManagerRegistry $managerRegistry;

    private UniqueResourceMetadataCollectionFactory $resourceMetadataFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->managerRegistry = $managerRegistry;
        /** @var UniqueResourceMetadataCollectionFactory $resourceMetadataFactory */
        $resourceMetadataFactory = static::getContainer()->get(UniqueResourceMetadataCollectionFactory::class);
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    public function testQueryBuilderHasAWhereOnInstance()
    {
        $filter = new DiscriminatorFilter(
            $this->managerRegistry,
            $this->resourceMetadataFactory
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->once())->method('getAllAliases')->willReturn(['o']);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('o INSTANCE OF :instance');
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('instance', 'people');

        $filter->apply($queryBuilderMock, new QueryNameGenerator(), User::class, null, ['filters' => [DiscriminatorFilter::FILTER_RESOURCE_TYPE_PROPERTY => 'People']]);
    }

    public function testQueryBuilderIsNotUpdated()
    {
        $filter = new DiscriminatorFilter(
            $this->managerRegistry,
            $this->resourceMetadataFactory
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->never())->method('getAllAliases');

        $filter->apply($queryBuilderMock, new QueryNameGenerator(), User::class, null, ['filters' => []]);
    }

    public function testQueryBuilderHasAWhereOneEqualZero()
    {
        $filter = new DiscriminatorFilter(
            $this->managerRegistry,
            $this->resourceMetadataFactory
        );

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->once())->method('where')->with('1=0');

        $filter->apply($queryBuilderMock, new QueryNameGenerator(), User::class, null, ['filters' => [DiscriminatorFilter::FILTER_RESOURCE_TYPE_PROPERTY => 'ForMyPeople']]);
    }

    public function testGetDescription()
    {
        $filter = new DiscriminatorFilter(
            $this->managerRegistry,
            $this->resourceMetadataFactory
        );

        self::assertSame([
            'resourceType' => [
                'property' => 'resourceType',
                'type' => 'string',
                'required' => false,
            ],
        ], $filter->getDescription(User::class));
    }
}
