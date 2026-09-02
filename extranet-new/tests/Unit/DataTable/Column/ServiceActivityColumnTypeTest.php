<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\ServiceActivityColumnType;
use App\Sdk\Resource\ServiceActivity;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class ServiceActivityColumnTypeTest extends ColumnTypeTestCase
{
    public function test(): void
    {
        $column = $this->createNamedColumn('serviceActivity');

        $rawDataObject = new \stdClass();
        $rawDataObject->serviceActivity = new ServiceActivity(
            iri: '/service_activity/42',
            id: 42,
            name: 'service activity 42',
            description: 'desc',
        );

        $valueView = $this->createColumnValueView($column, rowData: $rawDataObject);

        $this->assertSame('service activity 42', $valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new ServiceActivityColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TextColumnType(),
            new ColumnType(),
        ];
    }
}
