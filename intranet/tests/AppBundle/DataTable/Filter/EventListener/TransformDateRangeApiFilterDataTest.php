<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\EventListener;

use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Filter\Event\PreHandleEvent;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use PHPUnit\Framework\TestCase;

class TransformDateRangeApiFilterDataTest extends TestCase
{
    public function testFrom(): void
    {
        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $transformer = new TransformDateRangeApiFilterData();
        $filterData = new FilterData();
        $filter = $this->createMock(FilterInterface::class);

        $filterData->setValue([
            'from' => 'date from',
        ]);
        $event = new PreHandleEvent($apiProxyQuery, $filterData, $filter);
        $transformer->preHandle($event);

        $value = $event->getData()->getValue();
        $this->assertArrayHasKey('after', $value);
        $this->assertArrayNotHasKey('before', $value);
        $this->assertSame('date from', $value['after']);
        $this->assertSame(Operator::GreaterThanEquals, $event->getData()->getOperator());

        $this->assertSame(['kreyu_data_table.filter.pre_handle' => 'preHandle'], TransformDateRangeApiFilterData::getSubscribedEvents());
    }

    public function testTo(): void
    {
        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $transformer = new TransformDateRangeApiFilterData();
        $filterData = new FilterData();
        $filter = $this->createMock(FilterInterface::class);

        $filterData->setValue([
            'to' => 'date to',
        ]);
        $event = new PreHandleEvent($apiProxyQuery, $filterData, $filter);
        $transformer->preHandle($event);

        $value = $event->getData()->getValue();
        $this->assertArrayHasKey('before', $value);
        $this->assertArrayNotHasKey('after', $value);
        $this->assertSame('date to', $value['before']);
        $this->assertSame(Operator::LessThanEquals, $event->getData()->getOperator());
    }

    public function testFromTo(): void
    {
        $apiProxyQuery = $this->createMock(ApiProxyQuery::class);
        $transformer = new TransformDateRangeApiFilterData();
        $filterData = new FilterData();
        $filter = $this->createMock(FilterInterface::class);

        $filterData->setValue([
            'from' => 'date from',
            'to' => 'date to',
        ]);
        $event = new PreHandleEvent($apiProxyQuery, $filterData, $filter);
        $transformer->preHandle($event);

        $value = $event->getData()->getValue();
        $this->assertArrayHasKey('before', $value);
        $this->assertArrayHasKey('after', $value);
        $this->assertSame('date to', $value['before']);
        $this->assertSame('date from', $value['after']);
        $this->assertSame(Operator::Between, $event->getData()->getOperator());
    }
}
