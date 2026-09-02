<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Request;

use App\DataTable\Query\ApiProxyQuery;
use App\DataTable\Request\ApiHttpFoundationRequestHandler;
use Kreyu\Bundle\DataTableBundle\DataTableConfigInterface;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportData;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportStrategy;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group unit
 */
class ApiHttpFoundationRequestHandlerTest extends TestCase
{
    private ApiHttpFoundationRequestHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ApiHttpFoundationRequestHandler();
    }

    public function testFilterIsSkippedWhenFiltrationIsDisabled(): void
    {
        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: false, paginationEnabled: false);
        $dataTable->expects($this->never())->method('createFiltrationFormBuilder');

        $this->handler->handle($dataTable, $this->request());
    }

    public function testFilterAppliesWhenDataIsPresentInTheQueryString(): void
    {
        $submittedData = ['status' => 'PENDING'];
        $filtrationData = new FiltrationData();
        $form = $this->createForm('filter_technician_on_call', submittedWith: $submittedData, submitted: true, valid: true, data: $filtrationData);

        $dataTable = $this->createDataTable(filtrationEnabled: true, exportingEnabled: false, paginationEnabled: false);
        $dataTable->method('createFiltrationFormBuilder')->willReturn($this->formBuilder($form));
        $dataTable->expects($this->once())->method('filter')->with($filtrationData);

        $this->handler->handle($dataTable, $this->request(query: ['filter_technician_on_call' => $submittedData]));
    }

    public function testFilterIsIgnoredWhenDataIsOnlyInTheRequestBody(): void
    {
        $form = $this->createForm('filter_technician_on_call', submittedWith: null, submitted: false, valid: false, data: null);

        $dataTable = $this->createDataTable(filtrationEnabled: true, exportingEnabled: false, paginationEnabled: false);
        $dataTable->method('createFiltrationFormBuilder')->willReturn($this->formBuilder($form));
        $dataTable->expects($this->never())->method('filter');

        $this->handler->handle($dataTable, $this->request(method: 'POST', requestBody: ['filter_technician_on_call' => ['status' => 'PENDING']]));
    }

    public function testExportIsSkippedWhenExportingIsDisabled(): void
    {
        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: false, paginationEnabled: false);
        $dataTable->expects($this->never())->method('createExportFormBuilder');

        $this->handler->handle($dataTable, $this->request(method: 'POST'));
    }

    public function testExportIsIgnoredWhenDataIsOnlyInTheQueryString(): void
    {
        $form = $this->createForm('export_technician_on_call', submittedWith: null, submitted: false, valid: false, data: null);

        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: true, paginationEnabled: false);
        $dataTable->method('createExportFormBuilder')->willReturn($this->formBuilder($form));
        $dataTable->expects($this->never())->method('setExportData');

        $this->handler->handle($dataTable, $this->request(query: ['export_technician_on_call' => ['exporter' => 'csv']]));
    }

    public function testExportAppliesButDoesNotPropagateWhenTheQueryIsNotAnApiProxyQuery(): void
    {
        $submittedData = ['exporter' => 'csv'];
        $exportData = ExportData::fromArray(['filename' => 'technician_on_call', 'exporter' => 'csv', 'strategy' => ExportStrategy::IncludeCurrentPage]);
        $form = $this->createForm('export_technician_on_call', submittedWith: $submittedData, submitted: true, valid: true, data: $exportData);

        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: true, paginationEnabled: false);
        $dataTable->method('createExportFormBuilder')->willReturn($this->formBuilder($form));
        $dataTable->expects($this->once())->method('setExportData')->with($exportData);
        $dataTable->method('getExportData')->willReturn($exportData);
        $dataTable->method('getQuery')->willReturn($this->createMock(ProxyQueryInterface::class));

        $this->handler->handle($dataTable, $this->request(method: 'POST', requestBody: ['export_technician_on_call' => $submittedData]));
    }

    public function testExportPropagatesTheExportDataToTheApiProxyQuery(): void
    {
        $submittedData = ['exporter' => 'csv'];
        $exportData = ExportData::fromArray(['filename' => 'technician_on_call', 'exporter' => 'csv', 'strategy' => ExportStrategy::IncludeCurrentPage]);
        $form = $this->createForm('export_technician_on_call', submittedWith: $submittedData, submitted: true, valid: true, data: $exportData);
        $exporter = $this->createMock(ExporterInterface::class);

        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: true, paginationEnabled: false);
        $dataTable->method('createExportFormBuilder')->willReturn($this->formBuilder($form));
        $dataTable->expects($this->once())->method('setExportData')->with($exportData);
        $dataTable->method('getExportData')->willReturn($exportData);
        $dataTable->method('getExporter')->with('csv')->willReturn($exporter);

        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $apiProxyQuery->expects($this->once())->method('setExportData')->with($exportData, $exporter);
        $dataTable->method('getQuery')->willReturn($apiProxyQuery);

        $this->handler->handle($dataTable, $this->request(method: 'POST', requestBody: ['export_technician_on_call' => $submittedData]));
    }

    public function testPaginationIsSkippedWhileExporting(): void
    {
        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: false, paginationEnabled: true, exporting: true);
        $dataTable->expects($this->never())->method('paginate');

        $this->handler->handle($dataTable, $this->request(query: ['page' => 2, 'perPage' => 10]));
    }

    public function testPaginationRunsWhenNotExporting(): void
    {
        $dataTable = $this->createDataTable(filtrationEnabled: false, exportingEnabled: false, paginationEnabled: true, exporting: false);
        $dataTable->expects($this->once())
            ->method('paginate')
            ->with($this->callback(static fn (PaginationData $data): bool => 2 === $data->getPage() && 10 === $data->getPerPage()))
        ;

        $this->handler->handle($dataTable, $this->request(query: ['page' => 2, 'perPage' => 10]));
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $requestBody
     */
    private function request(string $method = 'GET', array $query = [], array $requestBody = []): Request
    {
        $request = Request::create('/', $method, $query);

        if ([] !== $requestBody) {
            $request->request->add($requestBody);
        }

        // handle() always calls setTurboFrameId() with the header's value, which the bundle's own
        // DataTableInterface declares as a non-nullable string — a request without this header would
        // otherwise make every mocked DataTableInterface::setTurboFrameId() call fail with a TypeError.
        $request->headers->set('Turbo-Frame', 'kreyu_data_table_technician_on_call');

        return $request;
    }

    private function createDataTable(bool $filtrationEnabled, bool $exportingEnabled, bool $paginationEnabled, bool $exporting = false): MockObject&DataTableInterface
    {
        $config = $this->createMock(DataTableConfigInterface::class);
        $config->method('isFiltrationEnabled')->willReturn($filtrationEnabled);
        $config->method('isExportingEnabled')->willReturn($exportingEnabled);
        $config->method('isPaginationEnabled')->willReturn($paginationEnabled);
        $config->method('isSortingEnabled')->willReturn(false);
        $config->method('getPageParameterName')->willReturn('page');
        $config->method('getPerPageParameterName')->willReturn('perPage');
        $config->method('getDefaultPaginationData')->willReturn(null);

        $dataTable = $this->createMock(DataTableInterface::class);
        $dataTable->method('getConfig')->willReturn($config);
        $dataTable->method('isExporting')->willReturn($exporting);
        $dataTable->method('setTurboFrameId')->willReturnSelf();

        return $dataTable;
    }

    private function formBuilder(FormInterface $form): MockObject&FormBuilderInterface
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('getForm')->willReturn($form);

        return $builder;
    }

    /**
     * @param array<string, mixed>|null $submittedWith
     */
    private function createForm(string $name, ?array $submittedWith, bool $submitted, bool $valid, mixed $data): MockObject&FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('getName')->willReturn($name);

        if (null !== $submittedWith) {
            $form->expects($this->once())->method('submit')->with($submittedWith)->willReturnSelf();
        } else {
            $form->expects($this->never())->method('submit');
        }

        $form->method('isSubmitted')->willReturn($submitted);
        $form->method('isValid')->willReturn($valid);
        $form->method('getData')->willReturn($data);

        return $form;
    }
}
