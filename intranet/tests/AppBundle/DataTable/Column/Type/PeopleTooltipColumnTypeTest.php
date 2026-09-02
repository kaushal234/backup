<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PeopleTooltipColumnTypeTest extends ColumnTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testNull()
    {
        $column = $this->createNamedColumn('people');
        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            '@id' => 42,
            'lastname' => 'Doe',
            'firstname' => 'John',
        ]));

        $this->assertSame('bundles/KreyuDataTableBundle/column/people_tooltip.html.twig', $valueView->vars['template_path']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new PeopleTooltipColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }
}
