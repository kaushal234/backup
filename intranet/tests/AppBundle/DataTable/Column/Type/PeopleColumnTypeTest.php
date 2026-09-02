<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PeopleColumnTypeTest extends ColumnTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testNull()
    {
        $this->urlGenerator = $this->createUrlGenerator();
        $this->urlGenerator->method('generate')->willReturn('/link/42');

        $column = $this->createNamedColumn('people');
        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            'people' => [
                '@id' => 42,
                'firstname' => 'John',
                'lastname' => 'Doe',
            ],
        ]));

        $this->assertSame('/link/42', $valueView->vars['href']);
        $this->assertSame('Doe John', $valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new PeopleColumnType(
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
