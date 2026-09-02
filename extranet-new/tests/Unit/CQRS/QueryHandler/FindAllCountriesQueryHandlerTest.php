<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler;

use App\CQRS\Query\FindAllCountriesQuery;
use App\CQRS\QueryHandler\FindAllCountriesQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\Country;
use PHPUnit\Framework\TestCase;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @group unit
 */
class FindAllCountriesQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $collection = $this->createMock(AccessibleCollectionInterface::class);
        $client
            ->expects($this->once())
            ->method('findAll')
            ->with(Country::class, ['query' => [
                'normalization_groups_override' => ['country_list'],
                'foo' => 'bar',
            ]])
            ->willReturn($collection)
        ;

        $queryHandler = new FindAllCountriesQueryHandler($client);
        $queryHandler->__invoke(new FindAllCountriesQuery(options: ['foo' => 'bar']));
    }
}
