<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use AppBundle\DataTable\Column\Type\DmsColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

class DmsColumnTypeTest extends ColumnTypeTestCase
{
    public function testBuildView()
    {
        $column = $this->createColumn([
            'route' => 'uno',
            'key' => 'dos',
        ]);
        $valueView = $this->createColumnValueView($column);

        $this->assertSame('uno', $valueView->vars['route']);
        $this->assertSame('dos', $valueView->vars['key']);
        $this->assertArrayHasKey('m', $valueView->vars['extra_params']);
        $this->assertSame(['help', 'dms'], $valueView->vars['extra_params']['m']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new DmsColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }
}
