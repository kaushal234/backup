<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Query;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingColumnData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Tests\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiProxyQueryTest extends TestCase
{
    use ReflectionTrait;

    public function testGetResult()
    {
        $client = $this->createClient();
        $query = $this->getApiProxyQuery($client);

        $client->expects($this->once())->method('findBy')->willReturn(new HydraCollection([
            'hydra:member' => [
                ['foo' => 'bar'],
                ['bar' => 'baz'],
            ],
            'hydra:totalItems' => 42,
        ]));

        $result = $query->getResult();

        $arrayResult = iterator_to_array($result->getIterator());
        $this->assertInstanceOf(ApiData::class, $arrayResult[0]);
        $this->assertSame(['bar' => 'baz'], $arrayResult[1]->toArray());

        $this->assertCount(2, $result);
        $this->assertSame(42, $result->getTotalItemCount());
        $this->assertSame(2, $result->getCurrentPageItemCount());
    }

    public function testSort()
    {
        $query = $this->getApiProxyQuery();

        $query->sort(new SortingData([
            new SortingColumnData('id', 'desc', '[id]'),
        ]));

        $this->assertSame('id', $this->getPrivatePropertyValue($query, 'sortName'));
        $this->assertSame('desc', $this->getPrivatePropertyValue($query, 'sortDirection'));
    }

    public function testPaginate()
    {
        $query = $this->getApiProxyQuery();

        $query->paginate(new PaginationData(page: 2, perPage: 1));

        $this->assertSame(2, $this->getPrivatePropertyValue($query, 'page'));
        $this->assertSame(1, $this->getPrivatePropertyValue($query, 'itemsPerPage'));
    }

    public function testSearch()
    {
        $query = $this->getApiProxyQuery();

        $query->search('Oh putain laurent');

        $this->assertSame(['q' => 'Oh putain laurent'], $this->getPrivatePropertyValue($query, 'filters'));
    }

    public function testFilter()
    {
        $query = $this->getApiProxyQuery();

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method('getQueryPath')->willReturn('query_path');

        $query->filter($filter, new FilterData('value'));

        $this->assertSame(['query_path' => 'value'], $this->getPrivatePropertyValue($query, 'filters'));
    }

    public function testQueryParameters()
    {
        $query = $this->getApiProxyQuery();

        $query->paginate(new PaginationData(page: 2, perPage: 1));

        $this->assertSame([
            'itemsPerPage' => 1,
            'page' => 2,
        ], $query->getQueryParameters());
    }

    public function testOrdersNull()
    {
        $query = $this->getApiProxyQuery();

        $this->assertSame([], $query->getOrders());
    }

    public function testOrders()
    {
        $query = $this->getApiProxyQuery();

        $query->sort(new SortingData([
            new SortingColumnData('id', 'desc', '[id]'),
        ]));

        $this->assertSame(['id' => 'desc'], $query->getOrders());
    }

    /**
     * ApiProxyQuery is meant to be driven exclusively through the data table bundle's
     * ProxyQueryInterface contract (sort/paginate/filter/existsFilter/getResult/search/
     * setExportData/export), plus a couple of already-@deprecated escape hatches
     * (getFilters/setFilter) kept only for pre-existing callers.
     *
     * A new public method appearing here is very often a sign that some controller or data
     * table type wants to reach into ApiProxyQuery's internals from outside the data table -
     * which should instead go through DataTableInterface::getFiltrationData()/getFilters(), or
     * through the filter/handler pipeline (see AppBundle\DataTable\Filter\Handler).
     *
     * If you added a method on purpose, add it to $expectedPublicMethods below - and double
     * check it's really meant to be called from outside the data table before doing so.
     */
    public function testPublicApiSurfaceIsClosed(): void
    {
        $expectedPublicMethods = [
            '__construct',
            'sort',
            'paginate',
            'filter',
            'existsFilter',
            'getResult',
            'search',
            'setExportData',
            'export',
            'getQueryParameters',
            'getFilters',
            'getOrders',
            'setFilter',
        ];

        $reflection = new \ReflectionClass(ApiProxyQuery::class);

        $actualPublicMethods = array_values(array_map(
            static fn (\ReflectionMethod $method): string => $method->getName(),
            array_filter(
                $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
                static fn (\ReflectionMethod $method): bool => ApiProxyQuery::class === $method->getDeclaringClass()->getName(),
            )
        ));

        sort($expectedPublicMethods);
        sort($actualPublicMethods);

        $this->assertSame(
            $expectedPublicMethods,
            $actualPublicMethods,
            "ApiProxyQuery's public API surface changed - update \$expectedPublicMethods above if this addition/removal is intentional."
        );
    }

    protected function getApiProxyQuery(?Client $client = null): ApiProxyQuery
    {
        if (null === $client) {
            $client = $this->createClient();
        }

        return new ApiProxyQuery('resource', $client, $this->createStub(FileStreamedResponseFactory::class), $this->createStub(CsvStreamedResponseFactory::class));
    }

    protected function createClient(): MockObject&Client
    {
        return $this->createMock(Client::class);
    }
}
