<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Formatter;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Exception\NotFoundValueException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class AutocompleteFilterFormatterTest extends TestCase
{
    public function testWithArray(): void
    {
        $client = $this->createMock(Client::class);
        $filter = $this->createMock(FilterInterface::class);
        $filterData = new FilterData([
            'id' => 42,
        ]);
        $formatter = new AutocompleteFilterFormatter($client, static fn ($value) => $value['id']);
        $result = $formatter($filterData, $filter);

        $this->assertSame(42, $result);
    }

    public function testWithApiData(): void
    {
        $client = $this->createMock(Client::class);
        $filter = $this->createMock(FilterInterface::class);
        $filterData = new FilterData(new ApiData([
            'id' => 42,
        ]));
        $formatter = new AutocompleteFilterFormatter($client, static fn ($value) => $value['id']);
        $result = $formatter($filterData, $filter);

        $this->assertSame(42, $result);
    }

    public function testWithEmpty(): void
    {
        $client = $this->createMock(Client::class);
        $filter = $this->createMock(FilterInterface::class);
        $filterData = new FilterData(new ApiData([]));
        $formatter = new AutocompleteFilterFormatter($client, static fn ($value) => $value['id']);
        $result = $formatter($filterData, $filter);

        $this->assertNull($result);
    }

    public function testWithApiResource(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('get')->willReturn(['id' => 42]);
        $filter = $this->createMock(FilterInterface::class);
        $filterData = new FilterData('/people/42');
        $formatter = new AutocompleteFilterFormatter($client, static fn ($value) => $value['id']);
        $result = $formatter($filterData, $filter);

        $this->assertSame(42, $result);
    }

    public function testWithApiException(): void
    {
        $this->expectException(NotFoundValueException::class);
        $this->expectExceptionMessage('Default value "/people/42" of filter "a cafe" not found, with API error: marche po');

        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(new \Exception('marche po'));
        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getFormName')->willReturn('a cafe');
        $filterData = new FilterData('/people/42');
        $formatter = new AutocompleteFilterFormatter($client, static fn ($value) => $value['id']);
        $result = $formatter($filterData, $filter);
    }
}
