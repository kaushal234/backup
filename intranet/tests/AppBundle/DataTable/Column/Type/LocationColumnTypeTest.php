<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocationColumnTypeTest extends ColumnTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testNull()
    {
        $this->urlGenerator = $this->createUrlGenerator();

        $column = $this->createNamedColumn('location');
        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            'location' => [
                '@id' => 42,
                'name' => 'My location',
            ],
        ]));

        $this->assertSame('My location', $valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new LocationColumnType(
            urlGenerator: $this->urlGenerator,
        );
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new LinkColumnType(),
            new TextColumnType(),
            new ColumnType(),
        ];
    }

    protected function createUrlGenerator(): MockObject&UrlGeneratorInterface
    {
        return $this->createMock(UrlGeneratorInterface::class);
    }
}
