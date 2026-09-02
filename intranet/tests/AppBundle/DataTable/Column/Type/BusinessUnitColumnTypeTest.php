<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BusinessUnitColumnTypeTest extends ColumnTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testNull()
    {
        $column = $this->createNamedColumn('business_unit');
        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            'businessUnit' => [
                '@id' => 42,
                'name' => 'My Business Unit',
            ],
        ]));

        $this->assertSame('My Business Unit', $valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new BusinessUnitColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TextColumnType(),
            new ColumnType(),
        ];
    }
}
