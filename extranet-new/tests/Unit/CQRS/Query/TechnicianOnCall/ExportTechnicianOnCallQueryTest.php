<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\TechnicianOnCall\ExportTechnicianOnCallQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ExportTechnicianOnCallQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new ExportTechnicianOnCallQuery('technician_on_call.csv', 'text/csv', ['columns' => 'id,title']);

        $this->assertSame('technician_on_call.csv', $query->filename);
        $this->assertSame('text/csv', $query->format);
        $this->assertSame(['columns' => 'id,title'], $query->options);
    }
}
