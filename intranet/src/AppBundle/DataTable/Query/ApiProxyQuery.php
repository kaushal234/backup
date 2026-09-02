<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Query;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
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
    private ?HydraCollection $data = null;
    private ?int $itemsPerPage = 25;
    private int $page = 1;
    private ?string $sortName = null;
    private ?string $sortDirection = null;
    private array $filters = [];
    private array $existsFilters = [];

    private string $exportFormat;
    private ExportData $exportData;

    private array $extraQueryParameters = [];

    public function __construct(
        private readonly string $resource,
        private readonly Client $client,
        private readonly FileStreamedResponseFactory $fileStreamedResponseFactory,
        private readonly CsvStreamedResponseFactory $csvStreamedResponseFactory,
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

    public function existsFilter(FilterInterface $filter, FilterData $filterData): void
    {
        $this->existsFilters[$filter->getQueryPath()] = $filterData->getValue();
    }

    public function getResult(): ResultSetInterface
    {
        if (!$this->data) {
            $this->data = $this->client->findBy(
                $this->resource,
                $this->getQueryParameters() + $this->filters + ['exists' => $this->existsFilters],
                $this->getOrders()
            );
        }

        return new ResultSet(
            iterator: new \ArrayIterator($this->data->all()),
            currentPageItemCount: $this->data->count(),
            totalItemCount: $this->data->pagination->getTotalItems(),
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
        $this->extraQueryParameters = $exporter->getConfig()->getOption('extra_query_parameters');
    }

    public function export(): Response
    {
        $parameters = [];

        if ('xlsx' === $this->exportData->exporter || 'csv' === $this->exportData->exporter) {
            $parameters['query']['pagination'] = false;
            $parameters['headers']['Accept'] = $this->exportFormat;
        }

        // Apply filters on export with strategy IncludeCurrentPage
        if ($this->exportData->strategy->value === ExportStrategy::IncludeCurrentPage->value) {
            $parameters['query'] += $this->filters + ['exists' => $this->existsFilters];
        }

        $parameters['query'] += $this->extraQueryParameters;

        return $this->getFileFactory()->create(
            $this->resource,
            $parameters,
            \sprintf('%s.%s', $this->exportData->filename, $this->exportData->exporter)
        );
    }

    public function getQueryParameters(): array
    {
        return [
            'itemsPerPage' => $this->itemsPerPage,
            'page' => $this->page,
        ];
    }

    /**
     * @deprecated Reading the raw, already-resolved query filters back off the proxy query
     *             couples callers to ApiProxyQuery's internal state. Use the data table's own
     *             DataTableInterface::getFilters() (filter definitions) together with
     *             DataTableInterface::getFiltrationData() (active values) instead - this works
     *             from outside the data table type without depending on ApiProxyQuery at all.
     *             Existing caller: ForecastClosureController::list().
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getOrders(): array
    {
        if (null === $this->sortName || null === $this->sortDirection) {
            return [];
        }

        return [
            $this->sortName => $this->sortDirection,
        ];
    }

    /**
     * @deprecated Filters are meant to be applied through the data table's own filtration
     *             pipeline (a FilterInterface/FilterHandlerInterface reacting to FiltrationData),
     *             not pushed onto the query from outside the data table. Existing internal
     *             handlers (e.g. AbstractPeopleLifecycleStatusFilterHandler, ChangeLogTypeFilterHandler)
     *             still use this to translate a filter's value into one or more raw query keys,
     *             but no new caller should reach into ApiProxyQuery directly from a controller
     *             or a data table type.
     */
    public function setFilter(string $key, mixed $value): void
    {
        $this->filters[$key] = $value;
    }

    private function getFileFactory(): FileStreamedResponseFactory|CsvStreamedResponseFactory
    {
        return match ($this->exportData->exporter) {
            'text/csv' => $this->csvStreamedResponseFactory,
            default => $this->fileStreamedResponseFactory,
        };
    }
}
