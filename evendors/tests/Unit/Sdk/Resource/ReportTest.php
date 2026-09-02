<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Resource;

use App\Sdk\Resource\Report;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class ReportTest extends TestCase
{
    public function testItSortsRowsByTotalDescending(): void
    {
        $report = new Report(
            iri: '/report',
            x: 'xField',
            total: 1000,
            rows: [
                'B' => [['value' => 20], ['value' => 30]],
                'A' => [['value' => 100]],
                'C' => [['value' => 5], ['value' => 5]],
            ]
        );

        $report->sortRowsByTotal('DESC');

        $expectedOrder = ['A', 'B', 'C'];
        self::assertSame($expectedOrder, array_keys($report->rows));
    }

    public function testItSortsRowsByTotalAscending(): void
    {
        $report = new Report(
            iri: '/report',
            x: 'xField',
            total: 1000,
            rows: [
                'B' => [['value' => 20], ['value' => 30]],
                'A' => [['value' => 100]],
                'C' => [['value' => 5], ['value' => 5]],
            ]
        );

        $report->sortRowsByTotal('ASC');

        $expectedOrder = ['C', 'B', 'A'];
        self::assertSame($expectedOrder, array_keys($report->rows));
    }

    public function testItHandlesMissingValuesGracefully(): void
    {
        $report = new Report(
            iri: '/report',
            x: 'xField',
            total: 1000,
            rows: [
                'X' => [['value' => null], []],
                'Y' => [['value' => 10]],
            ]
        );

        $report->sortRowsByTotal('DESC');

        $expectedOrder = ['Y', 'X'];
        self::assertSame($expectedOrder, array_keys($report->rows));
    }
}
