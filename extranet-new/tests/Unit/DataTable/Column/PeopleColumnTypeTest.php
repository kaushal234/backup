<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\PeopleColumnType;
use App\Sdk\Resource\People;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class PeopleColumnTypeTest extends ColumnTypeTestCase
{
    public function testNull(): void
    {
        $column = $this->createNamedColumn('people');
        $people = new People(
            iri: '/people/42',
            id: 42,
            lastname: 'Doe',
            firstname: 'John',
            email: 'john.doe@exemple.com',
        );
        $rawDataObject = new \stdClass();
        $rawDataObject->people = $people;

        $valueView = $this->createColumnValueView($column, rowData: $rawDataObject);

        $this->assertSame('Doe John', $valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new PeopleColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new LinkColumnType(),
            new TextColumnType(),
            new ColumnType(),
        ];
    }
}
