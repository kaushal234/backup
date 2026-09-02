<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Type\Purchasing;

use ApiBundle\Client;
use AppBundle\DataTable\Filter\Handler\ApiExistFilterHandler;
use AppBundle\DataTable\Filter\Handler\ApiFilterHandler;
use AppBundle\DataTable\Type\Purchasing\SupplierRankingFileDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Filter\FilterConfigInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupplierRankingFileDataTableTypeTest extends TestCase
{
    use ProphecyTrait;

    public function testBuildViewDoesNothingWhenCompletionReviewDisabled(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('get');

        $type = $this->createType($client);

        $view = new DataTableView();
        $type->buildView($view, $this->createStub(DataTableInterface::class), [
            'include_completion_review' => false,
        ]);

        $this->assertArrayNotHasKey('completion_review', $view->vars);
    }

    public function testBuildViewPopulatesCompletionReviewFromApiResponse(): void
    {
        $client = $this->createClientWithResponse(json_encode([
            'totalCompletionRate' => 87.5,
            'mandatoryCompletionRate' => 92.3,
        ]));

        $type = $this->createType($client);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn(null);
        $dataTable->method('getFilters')->willReturn([]);

        $view = new DataTableView();
        $type->buildView($view, $dataTable, ['include_completion_review' => true]);

        $this->assertSame([
            'totalCompletionRate' => 87.5,
            'mandatoryCompletionRate' => 92.3,
        ], $view->vars['completion_review']);
    }

    public function testBuildViewFallsBackToZeroOnClientException(): void
    {
        // A 5xx status would throw Symfony's ServerException instead - use 4xx so the response
        // actually raises the ClientException that SupplierRankingFileDataTableType::buildView()
        // catches.
        $client = $this->createClientWithResponse('', 400);

        $type = $this->createType($client);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn(null);
        $dataTable->method('getFilters')->willReturn([]);

        $view = new DataTableView();
        $type->buildView($view, $dataTable, ['include_completion_review' => true]);

        $this->assertSame([
            'totalCompletionRate' => 0,
            'mandatoryCompletionRate' => 0,
        ], $view->vars['completion_review']);
    }

    public function testBuildCompletionReviewQueryReturnsOnlyEmptyExistsWhenFiltrationDataIsNull(): void
    {
        $type = $this->createType($this->createStub(Client::class));

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn(null);

        $this->assertSame(
            ['exists' => []],
            $this->invokeBuildCompletionReviewQuery($type, $dataTable)
        );
    }

    public function testBuildCompletionReviewQuerySkipsInactiveFilters(): void
    {
        $type = $this->createType($this->createStub(Client::class));

        $filter = $this->createFilterStub('supplierName', 'supplier.name', new ApiFilterHandler());

        $filtrationData = new FiltrationData(['supplierName' => new FilterData(null)]);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn($filtrationData);
        $dataTable->method('getFilters')->willReturn(['supplierName' => $filter]);

        $this->assertSame(
            ['exists' => []],
            $this->invokeBuildCompletionReviewQuery($type, $dataTable)
        );
    }

    public function testBuildCompletionReviewQueryMapsRegularFilterToItsQueryPath(): void
    {
        $type = $this->createType($this->createStub(Client::class));

        $filter = $this->createFilterStub('supplierName', 'supplier.name', new ApiFilterHandler());

        $filtrationData = new FiltrationData(['supplierName' => new FilterData('Acme')]);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn($filtrationData);
        $dataTable->method('getFilters')->willReturn(['supplierName' => $filter]);

        $this->assertSame(
            ['exists' => [], 'supplier.name' => 'Acme'],
            $this->invokeBuildCompletionReviewQuery($type, $dataTable)
        );
    }

    public function testBuildCompletionReviewQueryBucketsExistFiltersUnderExistsKey(): void
    {
        $type = $this->createType($this->createStub(Client::class));

        $filter = $this->createFilterStub('supplierDisabled', 'disabledAt', new ApiExistFilterHandler());

        $filtrationData = new FiltrationData(['supplierDisabled' => new FilterData([0])]);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn($filtrationData);
        $dataTable->method('getFilters')->willReturn(['supplierDisabled' => $filter]);

        $this->assertSame(
            ['exists' => ['disabledAt' => [0]]],
            $this->invokeBuildCompletionReviewQuery($type, $dataTable)
        );
    }

    public function testBuildCompletionReviewQueryMapsSearchFilterToQKey(): void
    {
        $type = $this->createType($this->createStub(Client::class));

        $searchFilter = $this->createFilterStub(DataTableBuilderInterface::SEARCH_FILTER_NAME, 'irrelevant', new ApiFilterHandler());

        $filtrationData = new FiltrationData([
            DataTableBuilderInterface::SEARCH_FILTER_NAME => new FilterData('needle'),
        ]);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn($filtrationData);
        $dataTable->method('getFilters')->willReturn([
            DataTableBuilderInterface::SEARCH_FILTER_NAME => $searchFilter,
        ]);

        $this->assertSame(
            ['exists' => [], 'q' => 'needle'],
            $this->invokeBuildCompletionReviewQuery($type, $dataTable)
        );
    }

    public function testBuildCompletionReviewQueryCombinesRegularExistAndSearchFilters(): void
    {
        $type = $this->createType($this->createStub(Client::class));

        $nameFilter = $this->createFilterStub('supplierName', 'supplier.name', new ApiFilterHandler());
        $disabledFilter = $this->createFilterStub('supplierDisabled', 'disabledAt', new ApiExistFilterHandler());
        $searchFilter = $this->createFilterStub(DataTableBuilderInterface::SEARCH_FILTER_NAME, 'irrelevant', new ApiFilterHandler());
        $inactiveFilter = $this->createFilterStub('location', 'supplier.location', new ApiFilterHandler());

        $filtrationData = new FiltrationData([
            'supplierName' => new FilterData('Acme'),
            'supplierDisabled' => new FilterData([0]),
            DataTableBuilderInterface::SEARCH_FILTER_NAME => new FilterData('needle'),
            'location' => new FilterData(null),
        ]);

        $dataTable = $this->createStub(DataTableInterface::class);
        $dataTable->method('getFiltrationData')->willReturn($filtrationData);
        $dataTable->method('getFilters')->willReturn([
            'supplierName' => $nameFilter,
            'supplierDisabled' => $disabledFilter,
            DataTableBuilderInterface::SEARCH_FILTER_NAME => $searchFilter,
            'location' => $inactiveFilter,
        ]);

        $this->assertSame(
            [
                'exists' => ['disabledAt' => [0]],
                'supplier.name' => 'Acme',
                'q' => 'needle',
            ],
            $this->invokeBuildCompletionReviewQuery($type, $dataTable)
        );
    }

    private function createType(Client $client): SupplierRankingFileDataTableType
    {
        return new SupplierRankingFileDataTableType(
            $this->createStub(TranslatorInterface::class),
            $client,
            $this->createStub(UrlGeneratorInterface::class),
        );
    }

    private function createFilterStub(string $name, string $queryPath, object $handler): FilterInterface
    {
        $config = $this->createStub(FilterConfigInterface::class);
        $config->method('getHandler')->willReturn($handler);

        $filter = $this->createStub(FilterInterface::class);
        $filter->method('getName')->willReturn($name);
        $filter->method('getQueryPath')->willReturn($queryPath);
        $filter->method('getConfig')->willReturn($config);

        return $filter;
    }

    private function invokeBuildCompletionReviewQuery(SupplierRankingFileDataTableType $type, DataTableInterface $dataTable): array
    {
        $method = new \ReflectionMethod($type, 'buildCompletionReviewQuery');
        $method->setAccessible(true);

        return $method->invoke($type, $dataTable);
    }

    /**
     * Builds a real ApiBundle\Client wired to a MockHttpClient, mirroring the pattern used in
     * ClientExceptionMapperTest - lets SupplierRankingFileDataTableType::buildView() exercise
     * the actual HTTP call / ClientException-throwing behaviour instead of mocking Client::get()
     * directly.
     */
    private function createClientWithResponse(string $body, int $httpCode = 200): Client
    {
        $httpClient = new MockHttpClient([
            new MockResponse($body, [
                'response_headers' => ['content-type' => 'application/json'],
                'http_code' => $httpCode,
            ]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn(null);

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernelProphecy = $this->prophesize(KernelInterface::class);
        $kernelProphecy->getCacheDir()->willReturn('');
        $kernelProphecy->isDebug()->willReturn(false);

        return new Client(
            $securityProphecy->reveal(),
            $eventDispatcherProphecy->reveal(),
            $kernelProphecy->reveal(),
            $httpClient,
            [
                'base_uri' => 'https://example.com',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        );
    }
}
