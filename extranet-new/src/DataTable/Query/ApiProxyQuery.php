<?php

declare(strict_types=1);

namespace App\DataTable\Query;

use App\CQRS\Query\ExportableQueryInterface;
use App\CQRS\Query\QueryInterface;
use App\CQRS\QueryBusInterface;
use App\Sdk\Page;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportData;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportStrategy;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableBundle\Query\ResultSet;
use Kreyu\Bundle\DataTableBundle\Query\ResultSetInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Symfony\Component\HttpFoundation\Response;

class ApiProxyQuery implements ProxyQueryInterface
{
    private Page $data;
    private ?int $itemsPerPage = 25;
    private int $page = 1;
    private ?string $sortName = null;
    private ?string $sortDirection = null;
    private ?ExportData $exportData = null;
    private ?string $exportFormat = null;

    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<string, mixed>
     */
    private array $extraQueryParameters = [];

    public function __construct(
        private readonly QueryInterface $query,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function sort(SortingData $sortingData): void
    {
        foreach ($sortingData->getColumns() as $column) {
            $this->sortName = implode('.', $column->getPropertyPath()->getElements());
            $this->sortDirection = $column->getDirection();
        }
    }

    public function paginate(PaginationData $paginationData): void
    {
        $this->page = $paginationData->getPage();
        $this->itemsPerPage = $paginationData->getPerPage();
    }

    public function filter(FilterInterface $filter, FilterData $filterData): void
    {
        $this->filters[$filter->getQueryPath()] = $filterData->getValue();
    }

    public function getResult(): ResultSetInterface
    {
        if (property_exists($this->query, 'page')) {
            $this->query->page = $this->page;
        }

        if (property_exists($this->query, 'itemsPerPage')) {
            $this->query->itemsPerPage = $this->itemsPerPage;
        }

        // Merge filters and sorting from the DataTable with the original query options,
        // without overwriting them — original query options take precedence.
        if (property_exists($this->query, 'options')) {
            $this->query->options = array_merge(
                $this->query->options ?? [],
                $this->filters,
                ['order' => $this->getOrders()],
            );
        }

        $this->data = $this->queryBus->dispatch($this->query);

        return new ResultSet(
            iterator: new \ArrayIterator($this->data->items->toArray()),
            currentPageItemCount: $this->data->items->count(),
            totalItemCount: $this->data->totalItems,
        );
    }

    public function search(string $search): void
    {
        $this->filters['q'] = $search;
    }

    public function setExportData(ExportData $exportData, ExporterInterface $exporter): void
    {
        $this->exportData = $exportData;
        $this->exportFormat = $exporter->getConfig()->getOption('format');
        $this->extraQueryParameters = $exporter->getConfig()->getOption('extra_query_parameters', []);
    }

    public function export(): Response
    {
        if (!$this->query instanceof ExportableQueryInterface) {
            throw new \LogicException(\sprintf('Query "%s" does not support export.', $this->query::class));
        }

        if (null === $this->exportData) {
            throw new \LogicException('Cannot export before calling setExportData().');
        }

        $criteria = ExportStrategy::IncludeCurrentPage === $this->exportData->strategy
            ? [...$this->filters, 'order' => $this->getOrders()]
            : [];

        $criteria = [...$criteria, ...$this->extraQueryParameters];

        $filename = \sprintf('%s.%s', $this->exportData->filename, $this->exportData->exporter);

        $exportQuery = $this->query->toExportQuery($filename, $this->exportFormat, $criteria);

        return $this->queryBus->dispatch($exportQuery);
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrders(): array
    {
        if (null === $this->sortName || null === $this->sortDirection) {
            return [];
        }

        return [
            $this->sortName => $this->sortDirection,
        ];
    }
}
