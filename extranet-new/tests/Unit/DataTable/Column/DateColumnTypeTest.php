<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\DateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class DateColumnTypeTest extends ColumnTypeTestCase
{
    public function testBuildView(): void
    {
        $column = $this->createColumn();
        $valueView = $this->createColumnValueView($column);

        $this->assertSame('d-m-Y', $valueView->vars['format']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new DateColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new ColumnType(),
        ];
    }
}
