<?php

declare(strict_types=1);

namespace App\Tests\Manager\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Entity\Directory\People;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\MasterData\BusinessPartners\Buyer;
use App\ION\Resources\MasterData\BusinessPartners\Turnover;
use App\Manager\Purchasing\SupplierRanking\SupplierRankingManager;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SupplierRankingManagerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel([]);
    }

    public function testFindTurnoverWithSupplierCode()
    {
        $collectionDataProviderProphecy = $this->prophesize(CachedIONCollectionDataProvider::class);
        $itemDataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $turnover1 = new Turnover();
        $turnover1->code = 'ABC';
        $turnover2 = new Turnover();
        $turnover2->code = 'DEF';

        $operation = new GetCollection(class: Turnover::class);
        $resourceMetadataFactoryProphecy->create(Turnover::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Turnover::class, [new ApiResource(operations: [$operation])])
        );

        $date = new \DateTime();
        $collectionDataProviderProphecy->provide($operation, [], [
            'operation_type' => GetCollection::class,
            DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                'orderLineDateBefore' => $date->format('Y-m-d\TH:i:s\Z'),
                'orderLineDateAfter' => $date->modify('-2 year')->format('Y-m-d\TH:i:s\Z'),
            ],
        ])->shouldBeCalledOnce()->willReturn([$turnover1, $turnover2]);

        $manager = new SupplierRankingManager($collectionDataProviderProphecy->reveal(), $itemDataProviderProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal(), $entityManagerProphecy->reveal());
        $turnoverResultGood = $manager->findTurnoverWithSupplierCode('ABC');
        $turnoverResultBad = $manager->findTurnoverWithSupplierCode('ZER');

        $this->assertSame($turnoverResultGood, $turnover1);
        $this->assertFalse($turnoverResultBad);
    }

    public function testFindBuyer()
    {
        $collectionDataProviderProphecy = $this->prophesize(CachedIONCollectionDataProvider::class);
        $itemDataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);
        $peopleManagerMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();

        $buyer = new Buyer();
        $buyer->buyerCode = 'TEST';
        $people = new People();

        $operation = new Get(class: Buyer::class);
        $resourceMetadataFactoryProphecy->create(Buyer::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Buyer::class, [new ApiResource(operations: [$operation])])
        );

        $itemDataProviderProphecy->provide($operation, [
            'code' => 'TEST',
            'site' => 0,
        ])->shouldBeCalledOnce()->willReturn($buyer);

        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledOnce()->willReturn($peopleManagerMock);
        $peopleManagerMock->expects($this->once())->method('find')->with($this->callback(static fn ($string) => \is_string($string)))->willReturn($people);

        $manager = new SupplierRankingManager($collectionDataProviderProphecy->reveal(), $itemDataProviderProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal(), $entityManagerProphecy->reveal());

        $result = $manager->findBuyer('TEST', 0);
        $this->assertSame($result, $people);
    }

    public function testFindBuyerNull()
    {
        $collectionDataProviderProphecy = $this->prophesize(CachedIONCollectionDataProvider::class);
        $itemDataProviderProphecy = $this->prophesize(CachedIONItemDataProvider::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);

        $peopleManagerMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['find'])->getMock();

        $buyer = new Buyer();

        $operation = new Get(class: Buyer::class);
        $resourceMetadataFactoryProphecy->create(Buyer::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(Buyer::class, [new ApiResource(operations: [$operation])])
        );

        $itemDataProviderProphecy->provide($operation, [
            'code' => 'TEST',
            'site' => 0,
        ])->shouldBeCalledOnce()->willReturn($buyer);

        $entityManagerProphecy->getRepository(People::class)->shouldNotBeCalled();
        $peopleManagerMock->expects($this->never())->method('find');

        $manager = new SupplierRankingManager($collectionDataProviderProphecy->reveal(), $itemDataProviderProphecy->reveal(), $resourceMetadataFactoryProphecy->reveal(), $entityManagerProphecy->reveal());

        $result = $manager->findBuyer('TEST', 0);
        $this->assertNull($result);
    }
}
