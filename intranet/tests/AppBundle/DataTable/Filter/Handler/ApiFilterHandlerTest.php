<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Handler;

use AppBundle\DataTable\Filter\Handler\ApiFilterHandler;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ArrayProxyQuery;
use PHPUnit\Framework\TestCase;

class ApiFilterHandlerTest extends TestCase
{
    public function testException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $handler = new ApiFilterHandler();

        $handler->handle(new ArrayProxyQuery([]), new FilterData(), $this->createStub(FilterInterface::class));
    }

    public function testHandle()
    {
        $handler = new ApiFilterHandler();

        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $apiProxyQuery->expects($this->once())->method('filter');

        $handler->handle($apiProxyQuery, new FilterData(), $this->createStub(FilterInterface::class));
    }
}
