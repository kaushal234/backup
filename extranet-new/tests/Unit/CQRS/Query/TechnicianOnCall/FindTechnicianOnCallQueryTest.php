<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindTechnicianOnCallQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindTechnicianOnCallQuery(42);

        $this->assertSame(42, $query->id);
    }
}
