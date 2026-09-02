<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\IndiceFactorColumnType;
use App\DataTable\Column\LabelColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class IndiceFactorColumnTypeTest extends ColumnTypeTestCase
{
    public function test(): void
    {
        $column = $this->createNamedColumn('indiceFactor');

        $object = new \stdClass();
        $object->indiceFactor = 'IF 10';

        $columnValueView = $this->createColumnValueView(
            $column,
            rowData: $object
        );

        $this->assertSame('primary', $columnValueView->vars['label_classes']['IF 10']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new IndiceFactorColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new LabelColumnType(),
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }
}
