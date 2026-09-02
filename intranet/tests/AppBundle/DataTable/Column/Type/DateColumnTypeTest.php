<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use AppBundle\DataTable\Column\Type\DateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

class DateColumnTypeTest extends ColumnTypeTestCase
{
    public function testBuildView()
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
