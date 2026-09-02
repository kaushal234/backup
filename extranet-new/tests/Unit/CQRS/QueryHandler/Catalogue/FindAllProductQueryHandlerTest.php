<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductsQuery;
use App\CQRS\QueryHandler\Catalogue\FindAllProductQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\Product;
use PHPUnit\Framework\TestCase;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @group unit
 */
class FindAllProductQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $collection = $this->createMock(AccessibleCollectionInterface::class);
        $client
            ->expects($this->once())
            ->method('findAll')
            ->with(Product::class, ['query' => ['foo' => 'bar']])
            ->willReturn($collection)
        ;

        $queryHandler = new FindAllProductQueryHandler($client);
        $queryHandler->__invoke(new FindAllProductsQuery(options: ['foo' => 'bar']));
    }
}
