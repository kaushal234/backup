<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\DepartmentColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DepartmentColumnTypeTest extends ColumnTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testDepartmentLink()
    {
        $this->urlGenerator = $this->createUrlGenerator();
        $this->urlGenerator->method('generate')->willReturn('/directory/departments/42/show');

        $column = $this->createColumn();
        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            'department' => [
                '@id' => '42',
                'name' => 'Finance',
            ],
        ]));

        $this->assertSame('/directory/departments/42/show', $valueView->vars['href']);
        $this->assertSame('Finance', $valueView->vars['value']);
    }

    public function testNull()
    {
        $this->urlGenerator = $this->createUrlGenerator();

        $column = $this->createColumn();
        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            'department' => null,
        ]));

        $this->assertNull($valueView->vars['href']);
        $this->assertNull($valueView->vars['value']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new DepartmentColumnType(
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
