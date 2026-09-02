<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\TechnicianOnCall\ExportTechnicianOnCallQuery;
use App\CQRS\Query\TechnicianOnCall\FindAllTechnicianOnCallQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllTechnicianOnCallQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindAllTechnicianOnCallQuery(page: 7, itemsPerPage: 42, options: ['foo' => 'bar']);

        $this->assertSame(7, $query->page);
        $this->assertSame(42, $query->itemsPerPage);
        $this->assertSame(['foo' => 'bar'], $query->options);
    }

    public function testToExportQueryMergesOwnOptionsWithGivenCriteria(): void
    {
        $query = new FindAllTechnicianOnCallQuery(options: ['status' => ['PENDING']]);

        $exportQuery = $query->toExportQuery('technician_on_call.csv', 'text/csv', ['columns' => 'id,title']);

        $this->assertInstanceOf(ExportTechnicianOnCallQuery::class, $exportQuery);
        $this->assertSame('technician_on_call.csv', $exportQuery->filename);
        $this->assertSame('text/csv', $exportQuery->format);
        $this->assertSame(['status' => ['PENDING'], 'columns' => 'id,title'], $exportQuery->options);
    }

    public function testToExportQueryCriteriaTakesPrecedenceOverOwnOptionsOnConflict(): void
    {
        $query = new FindAllTechnicianOnCallQuery(options: ['columns' => 'id']);

        $exportQuery = $query->toExportQuery('technician_on_call.csv', 'text/csv', ['columns' => 'id,title']);

        $this->assertInstanceOf(ExportTechnicianOnCallQuery::class, $exportQuery);
        $this->assertSame(['columns' => 'id,title'], $exportQuery->options);
    }
}
