<?php

declare(strict_types=1);

namespace App\DataTable\Request;

use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationInterface;
use Kreyu\Bundle\DataTableBundle\Request\RequestHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ApiHttpFoundationRequestHandler implements RequestHandlerInterface
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function handle(DataTableInterface $dataTable, mixed $request = null): void
    {
        if (null === $request) {
            return;
        }

        if (!$request instanceof Request) {
            throw new UnexpectedTypeException($request, Request::class);
        }

        $this->filter($dataTable, $request);
        $this->sort($dataTable, $request);
        $this->export($dataTable, $request);

        if (!$dataTable->isExporting()) {
            $this->paginate($dataTable, $request);
        }

        $this->turbo($dataTable, $request);
    }

    private function filter(DataTableInterface $dataTable, Request $request): void
    {
        if (!$dataTable->getConfig()->isFiltrationEnabled()) {
            return;
        }

        $form = $dataTable->createFiltrationFormBuilder()->getForm();

        if ($data = $request->query->all($form->getName())) {
            $form->submit($data);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $dataTable->filter($form->getData());
        }
    }

    private function sort(DataTableInterface $dataTable, Request $request): void
    {
        if (!$dataTable->getConfig()->isSortingEnabled()) {
            return;
        }

        $parameterName = $dataTable->getConfig()->getSortParameterName();

        $sortingData = $this->extractQueryParameter($request, "[$parameterName]");

        if (empty($sortingData)) {
            return;
        }

        $dataTable->sort(SortingData::fromArray($sortingData));
    }

    private function paginate(DataTableInterface $dataTable, Request $request): void
    {
        if (!$dataTable->getConfig()->isPaginationEnabled()) {
            return;
        }

        $defaultPaginationData = $dataTable->getConfig()->getDefaultPaginationData();

        $pageParameterName = $dataTable->getConfig()->getPageParameterName();
        $perPageParameterName = $dataTable->getConfig()->getPerPageParameterName();

        $page = $this->extractQueryParameter($request, "[$pageParameterName]");
        $perPage = $this->extractQueryParameter($request, "[$perPageParameterName]");

        $perPage ??= $defaultPaginationData?->getPerPage() ?? PaginationInterface::DEFAULT_PER_PAGE;

        if (null === $page) {
            return;
        }

        $dataTable->paginate(new PaginationData((int) $page, (int) $perPage));
    }

    private function export(DataTableInterface $dataTable, Request $request): void
    {
        if (!$dataTable->getConfig()->isExportingEnabled()) {
            return;
        }

        $form = $dataTable->createExportFormBuilder()->getForm();

        if ($data = $request->request->all($form->getName())) {
            $form->submit($data);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $dataTable->setExportData($form->getData());

            $exportData = $dataTable->getExportData();
            $query = $dataTable->getQuery();

            if ($query instanceof ApiProxyQuery && null !== $exportData) {
                $query->setExportData($exportData, $dataTable->getExporter($exportData->exporter));
            }
        }
    }

    private function extractQueryParameter(Request $request, string $path): mixed
    {
        return $this->propertyAccessor->getValue($request->query->all(), $path);
    }

    private function turbo(DataTableInterface $dataTable, Request $request): void
    {
        $dataTable->setTurboFrameId($request->headers->get('Turbo-Frame'));
    }
}
