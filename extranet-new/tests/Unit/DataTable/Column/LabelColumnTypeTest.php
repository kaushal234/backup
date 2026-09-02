<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\LabelColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class LabelColumnTypeTest extends ColumnTypeTestCase
{
    public function testArray(): void
    {
        $column = $this->createNamedColumn('label', [
            'text_values' => ['william' => 'ne sait toujours pas coder'],
            'label_classes' => ['bah' => 'dis donc'],
        ]);

        $columnValueView = $this->createColumnValueView($column);

        $this->assertSame(['william' => 'ne sait toujours pas coder'], $columnValueView->vars['text_values']);
        $this->assertSame(['bah' => 'dis donc'], $columnValueView->vars['label_classes']);
    }

    public function testCallable(): void
    {
        $column = $this->createNamedColumn('label', [
            'text_values' => static fn (\stdClass $object) => $object->label->test1.' '.$object->label->test2,
            'label_classes' => static fn (\stdClass $object) => [$object->label->test1 => $object->label->test2],
        ]);

        $object = new \stdClass();
        $object->test1 = 'oh putain';
        $object->test2 = 'laurent';
        $rawDataObject = new \stdClass();
        $rawDataObject->label = $object;

        $columnValueView = $this->createColumnValueView(
            $column,
            rowData: $rawDataObject
        );

        $this->assertSame('oh putain laurent', $columnValueView->vars['text_values']);
        $this->assertSame('laurent', $columnValueView->vars['label_classes']['oh putain']);
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
