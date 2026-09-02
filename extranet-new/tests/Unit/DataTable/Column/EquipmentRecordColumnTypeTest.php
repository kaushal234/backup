<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\EquipmentRecordColumnType;
use App\Sdk\Resource\EquipmentRecord;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\HtmlColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class EquipmentRecordColumnTypeTest extends ColumnTypeTestCase
{
    public function test(): void
    {
        $column = $this->createNamedColumn('equipmentRecord');

        $rawDataObject = new \stdClass();
        $rawDataObject->equipmentRecord = new EquipmentRecord(
            iri: '/equipment_records/42',
            id: 42,
            serialNumber: 'T424242',
            product: 'test product',
            legacyId: 4242,
        );

        $valueView = $this->createColumnValueView($column, rowData: $rawDataObject);

        $this->assertSame('test product <br/> T424242', $valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new EquipmentRecordColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new HtmlColumnType(),
            new ColumnType(),
        ];
    }
}
