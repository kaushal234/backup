<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Query;

use App\CQRS\QueryBusInterface;
use App\DataTable\Query\ApiProxyQuery;
use App\Sdk\Page;
use App\Tests\Unit\DummyExportableQuery;
use App\Tests\Unit\DummyQuery;
use App\Tests\Unit\DummyResource;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportData;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterConfigInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportStrategy;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingColumnData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Tests\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psl\Collection\MutableVector;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group unit
 */
class ApiProxyQueryTest extends TestCase
{
    use ReflectionTrait;

    public function testGetResult(): void
    {
        $queryBus = $this->createQueryBus();
        $query = $this->getApiProxyQuery($queryBus);

        $items = new MutableVector([new DummyResource('foo'), new DummyResource('bar')]);
        $queryBus->expects($this->once())->method('dispatch')->willReturn(new Page(
            page: 1,
            itemsPerPage: 25,
            totalItems: 42,
            hasNext: true,
            hasPrevious: false,
            items: $items));

        $result = $query->getResult();

        $arrayResult = iterator_to_array($result->getIterator());
        $this->assertInstanceOf(DummyResource::class, $arrayResult[0]);
        $this->assertSame('bar', $arrayResult[1]->name);

        $this->assertCount(2, $result);
        $this->assertSame(42, $result->getTotalItemCount());
        $this->assertSame(2, $result->getCurrentPageItemCount());
    }

    public function testSort(): void
    {
        $query = $this->getApiProxyQuery();

        $query->sort(new SortingData([
            new SortingColumnData('id', 'desc', '[id]'),
        ]));

        $this->assertSame('id', $this->getPrivatePropertyValue($query, 'sortName'));
        $this->assertSame('desc', $this->getPrivatePropertyValue($query, 'sortDirection'));
    }

    public function testPaginate(): void
    {
        $query = $this->getApiProxyQuery();

        $query->paginate(new PaginationData(page: 2, perPage: 1));

        $this->assertSame(2, $this->getPrivatePropertyValue($query, 'page'));
        $this->assertSame(1, $this->getPrivatePropertyValue($query, 'itemsPerPage'));
    }

    public function testSearch(): void
    {
        $query = $this->getApiProxyQuery();

        $query->search('Oh putain laurent');

        $this->assertSame(['q' => 'Oh putain laurent'], $this->getPrivatePropertyValue($query, 'filters'));
    }

    public function testFilter(): void
    {
        $query = $this->getApiProxyQuery();

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method('getQueryPath')->willReturn('query_path');

        $query->filter($filter, new FilterData('value'));

        $this->assertSame(['query_path' => 'value'], $this->getPrivatePropertyValue($query, 'filters'));
    }

    public function testOrdersNull(): void
    {
        $query = $this->getApiProxyQuery();

        $this->assertSame([], $query->getOrders());
    }

    public function testOrders(): void
    {
        $query = $this->getApiProxyQuery();

        $query->sort(new SortingData([
            new SortingColumnData('id', 'desc', '[id]'),
        ]));

        $this->assertSame(['id' => 'desc'], $query->getOrders());
    }

    public function testGetResultMergesFiltersAndPreservesQueryOptions(): void
    {
        $queryBus = $this->createQueryBus();

        $dummyQuery = new DummyQuery();
        $dummyQuery->options = ['preserved' => 'original'];

        $apiQuery = new ApiProxyQuery($dummyQuery, $queryBus);

        // Inject some filters + sorting
        $this->setPrivatePropertyValue($apiQuery, 'filters', ['foo' => 'bar']);
        $this->setPrivatePropertyValue($apiQuery, 'sortName', 'id');
        $this->setPrivatePropertyValue($apiQuery, 'sortDirection', 'asc');

        $items = new MutableVector([new DummyResource('x')]);
        $queryBus->expects($this->once())->method('dispatch')->willReturn(new Page(
            page: 1,
            itemsPerPage: 25,
            totalItems: 1,
            hasNext: false,
            hasPrevious: false,
            items: $items,
        ));

        $apiQuery->getResult();

        $expectedOptions = [
            'preserved' => 'original', // from DummyQuery
            'foo' => 'bar',            // from filters
            'order' => ['id' => 'asc'], // from sorting
        ];

        $this->assertSame($expectedOptions, $dummyQuery->options);
    }

    public function testSetExportDataStoresExporterOptions(): void
    {
        $query = $this->getApiProxyQuery();

        $exportData = ExportData::fromArray([
            'filename' => 'export',
            'exporter' => 'csv',
            'strategy' => ExportStrategy::IncludeAll,
        ]);
        $exporter = $this->createExporter('text/csv', ['columns' => 'id,title']);

        $query->setExportData($exportData, $exporter);

        $this->assertSame($exportData, $this->getPrivatePropertyValue($query, 'exportData'));
        $this->assertSame('text/csv', $this->getPrivatePropertyValue($query, 'exportFormat'));
        $this->assertSame(['columns' => 'id,title'], $this->getPrivatePropertyValue($query, 'extraQueryParameters'));
    }

    public function testExportThrowsWhenQueryDoesNotSupportExport(): void
    {
        $queryBus = $this->createQueryBus();
        $queryBus->expects($this->never())->method('dispatch');

        $query = new ApiProxyQuery(new DummyQuery(), $queryBus);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Query "App\Tests\Unit\DummyQuery" does not support export.');

        $query->export();
    }

    public function testExportThrowsWhenExportDataWasNeverSet(): void
    {
        $queryBus = $this->createQueryBus();
        $queryBus->expects($this->never())->method('dispatch');

        $query = new ApiProxyQuery(new DummyExportableQuery(), $queryBus);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot export before calling setExportData().');

        $query->export();
    }

    public function testExportWithIncludeCurrentPageStrategyRespectsActiveFiltersAndSorting(): void
    {
        $queryBus = $this->createQueryBus();
        $exportableQuery = new DummyExportableQuery();
        $query = new ApiProxyQuery($exportableQuery, $queryBus);

        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getQueryPath')->willReturn('status');
        $query->filter($filter, new FilterData('PENDING'));
        $query->sort(new SortingData([new SortingColumnData('id', 'asc', '[id]')]));

        $exportData = ExportData::fromArray([
            'filename' => 'technician_on_call',
            'exporter' => 'csv',
            'strategy' => ExportStrategy::IncludeCurrentPage,
        ]);
        $query->setExportData($exportData, $this->createExporter('text/csv', ['columns' => 'id,title']));

        $response = $this->createMock(Response::class);
        $queryBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(static fn ($dispatched) => $dispatched instanceof DummyQuery))
            ->willReturn($response)
        ;

        $this->assertSame($response, $query->export());
        $this->assertSame('technician_on_call.csv', $exportableQuery->exportFilename);
        $this->assertSame('text/csv', $exportableQuery->exportFormat);
        $this->assertSame([
            'status' => 'PENDING',
            'order' => ['id' => 'asc'],
            'columns' => 'id,title',
        ], $exportableQuery->exportCriteria);
    }

    public function testExportWithIncludeAllStrategyIgnoresActiveFilters(): void
    {
        $queryBus = $this->createQueryBus();
        $exportableQuery = new DummyExportableQuery();
        $query = new ApiProxyQuery($exportableQuery, $queryBus);

        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getQueryPath')->willReturn('status');
        $query->filter($filter, new FilterData('PENDING'));

        $exportData = ExportData::fromArray([
            'filename' => 'technician_on_call',
            'exporter' => 'xlsx',
            'strategy' => ExportStrategy::IncludeAll,
        ]);
        $query->setExportData($exportData, $this->createExporter('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ['columns' => 'id,title']));

        $queryBus->method('dispatch')->willReturn($this->createMock(Response::class));

        $query->export();

        $this->assertSame(['columns' => 'id,title'], $exportableQuery->exportCriteria);
        $this->assertSame('technician_on_call.xlsx', $exportableQuery->exportFilename);
    }

    /**
     * @param array<string, mixed> $extraQueryParameters
     */
    protected function createExporter(string $format, array $extraQueryParameters): MockObject&ExporterInterface
    {
        $config = $this->createMock(ExporterConfigInterface::class);
        $config->method('getOption')->willReturnCallback(
            static fn (string $name, mixed $default = null): mixed => match ($name) {
                'format' => $format,
                'extra_query_parameters' => $extraQueryParameters,
                default => $default,
            }
        );

        $exporter = $this->createMock(ExporterInterface::class);
        $exporter->method('getConfig')->willReturn($config);

        return $exporter;
    }

    protected function getApiProxyQuery(?QueryBusInterface $queryBus = null): ApiProxyQuery
    {
        if (null === $queryBus) {
            $queryBus = $this->createQueryBus();
        }

        return new ApiProxyQuery(new DummyQuery(), $queryBus);
    }

    protected function createQueryBus(): MockObject&QueryBusInterface
    {
        return $this->createMock(QueryBusInterface::class);
    }
}
