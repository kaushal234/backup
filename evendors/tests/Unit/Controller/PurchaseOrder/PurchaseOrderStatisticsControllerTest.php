<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\PurchaseOrder;

use App\Controller\PurchaseOrder\PurchaseOrderStatisticsController;
use App\CQRS\QueryBusInterface;
use App\Statistic\Provider\PurchaseOrderStatisticProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group unit
 */
class PurchaseOrderStatisticsControllerTest extends TestCase
{
    private PurchaseOrderStatisticsController $controller;

    protected function setUp(): void
    {
        $statistics = new PurchaseOrderStatisticProvider($this->createMock(QueryBusInterface::class));
        $this->controller = new PurchaseOrderStatisticsController($statistics);
    }

    public function testInvokeReturnsExpectedStats(): void
    {
        $request = Request::create('/PurchaseOrderStatistics', 'GET');
        $response = $this->controller->__invoke($request);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('stats', $response);
        $this->assertIsArray($response['stats']);
        $expectedKeys = ['Late', 'Unconfirmed', 'To be delivered in 7 days', 'Total'];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $response['stats']);
        }

        foreach ($response['stats'] as $statValue) {
            $this->assertIsInt($statValue);
            $this->assertGreaterThanOrEqual(0, $statValue);
        }
    }
}
