<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Handler;

use App\DataTable\Filter\Handler\ApiFilterHandler;
use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterConfigInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ArrayProxyQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ApiFilterHandlerTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $handler = new ApiFilterHandler();

        $handler->handle(new ArrayProxyQuery([]), new FilterData(), $this->createStub(FilterInterface::class));
    }

    public function testHandleNoValue(): void
    {
        $handler = new ApiFilterHandler();

        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $apiProxyQuery->expects($this->never())->method('filter');

        $handler->handle($apiProxyQuery, new FilterData(), $this->createStub(FilterInterface::class));
    }

    public function testHandleWithoutValueExtractor(): void
    {
        $handler = new ApiFilterHandler();

        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $apiProxyQuery
            ->expects($this->once())
            ->method('filter')
            ->with(
                $this->callback(static fn ($arg) => $arg instanceof FilterInterface),
                $this->callback(static fn (FilterData $d) => 'value' === $d->getValue())
            )
        ;

        $data = new FilterData();
        $data->setValue('value');

        $handler->handle($apiProxyQuery, $data, $this->createStub(FilterInterface::class));
    }

    public function testAppliesExtractorOnSingleValue(): void
    {
        $handler = new ApiFilterHandler();
        $extractor = static fn ($value) => mb_strtoupper($value);

        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $apiProxyQuery
            ->expects($this->once())
            ->method('filter')
            ->with(
                $this->callback(static fn ($arg) => $arg instanceof FilterInterface),
                $this->callback(static fn (FilterData $d) => 'TEST' === $d->getValue())
            )
        ;

        $filter = $this->createConfiguredMock(FilterInterface::class, [
            'getConfig' => $this->createFilterConfigMock($extractor),
        ]);

        $data = new FilterData('test');

        $handler->handle($apiProxyQuery, $data, $filter);
    }

    public function testAppliesExtractorOnIterableValue(): void
    {
        $handler = new ApiFilterHandler();
        $extractor = static fn ($value) => $value * 2;

        $query = $this->createMock(ApiProxyQuery::class);
        $query
            ->expects($this->once())
            ->method('filter')
            ->with(
                $this->callback(static fn ($arg) => $arg instanceof FilterInterface),
                $this->callback(static function (FilterData $d) {
                    return $d->getValue() === [2, 4, 6];
                })
            )
        ;

        $filter = $this->createConfiguredMock(FilterInterface::class, [
            'getConfig' => $this->createFilterConfigMock($extractor),
        ]);

        $data = new FilterData([1, 2, 3]);

        $handler->handle($query, $data, $filter);
    }

    private function createFilterConfigMock(?callable $extractor): object
    {
        $config = $this->getMockBuilder(FilterConfigInterface::class)->getMock();

        $config->method('getOption')
            ->with('value_extractor')
            ->willReturn($extractor);

        return $config;
    }
}
