<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

class LabelColumnTypeTest extends ColumnTypeTestCase
{
    public function testArray()
    {
        $column = $this->createColumn([
            'text_values' => ['william' => 'ne sait toujours pas coder'],
            'label_classes' => ['bah' => 'dis donc'],
        ]);

        $columnValueView = $this->createColumnValueView($column);

        $this->assertSame(['william' => 'ne sait toujours pas coder'], $columnValueView->vars['text_values']);
        $this->assertSame(['bah' => 'dis donc'], $columnValueView->vars['label_classes']);
    }

    public function testCallable()
    {
        $column = $this->createColumn([
            'text_values' => static fn ($value, $vars) => $vars['test1'].' '.$vars['test2'],
            'label_classes' => static fn ($value, $vars) => [$vars['test1'] => $vars['test2']],
        ]);

        $columnValueView = $this->createColumnValueView(
            $column,
            rowData: new ApiData([
                'test1' => 'oh putain',
                'test2' => 'laurent',
            ])
        );

        $this->assertSame('oh putain laurent', $columnValueView->vars['text_values']);
        $this->assertSame('laurent', $columnValueView->vars['label_classes']['oh putain']);
    }

    public function testColorCallable()
    {
        $column = $this->createColumn([
            'color' => static fn ($value, $vars) => [$vars['test1'] => $vars['test2']],
        ]);

        $columnValueView = $this->createColumnValueView(
            $column,
            rowData: new ApiData([
                'test1' => 'oh putain',
                'test2' => 'laurent',
            ])
        );

        $this->assertSame('laurent', $columnValueView->vars['color']['oh putain']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new LabelColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }
}
