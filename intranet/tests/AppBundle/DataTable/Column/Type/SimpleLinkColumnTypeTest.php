<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\SimpleLinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SimpleLinkColumnTypeTest extends ColumnTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testRoute()
    {
        $this->urlGenerator = $this->createUrlGenerator();
        $this->urlGenerator->method('generate')->willReturn('/kittens/42');

        $column = $this->createColumn([
            'route' => 'test_route_one_param',
        ]);

        $valueView = $this->createColumnValueView($column, rowData: new ApiData([
            'id' => '42',
        ]));

        $this->assertSame('/kittens/42', $valueView->vars['href']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new SimpleLinkColumnType(
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
