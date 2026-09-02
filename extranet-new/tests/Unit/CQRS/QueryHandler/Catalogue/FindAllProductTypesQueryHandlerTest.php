<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductTypesQuery;
use App\CQRS\QueryHandler\Catalogue\FindAllProductTypesQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\ProductType;
use PHPUnit\Framework\TestCase;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @group unit
 */
class FindAllProductTypesQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $collection = $this->createMock(AccessibleCollectionInterface::class);
        $client
            ->expects($this->once())
            ->method('findAll')
            ->with(ProductType::class, ['query' => ['foo' => 'bar', 'order' => ['englishName' => 'ASC']]])
            ->willReturn($collection)
        ;

        $queryHandler = new FindAllProductTypesQueryHandler($client);
        $queryHandler->__invoke(new FindAllProductTypesQuery(options: ['foo' => 'bar']));
    }
}
