<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use AppBundle\DataTable\Column\Type\LongListColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

class LongListColumnTypeTest extends ColumnTypeTestCase
{
    public function testLabelsAreFormattedAndNotTruncatedWhenUnderLimit()
    {
        $column = $this->createColumn([
            'property_path' => '[data]',
            'item_formatter' => static fn (array $item) => $item['code'],
            'limit' => 8,
        ]);

        $valueView = $this->createColumnValueView($column, rowData: [
            'data' => [
                ['code' => 'CDG'],
                ['code' => 'BDX'],
            ],
        ]);

        $this->assertSame(['CDG', 'BDX'], $valueView->vars['template_vars']['labels']);
        $this->assertNull($valueView->vars['template_vars']['grouped_labels']);
    }

    public function testItemsAreSortedByDottedSortKeyBeforeFormatting()
    {
        $column = $this->createColumn([
            'property_path' => '[data]',
            'item_formatter' => static fn (array $item) => $item['code'],
            'item_sort_key' => 'cityName',
        ]);

        $valueView = $this->createColumnValueView($column, rowData: [
            'data' => [
                ['code' => 'CDG', 'cityName' => 'Paris'],
                ['code' => 'BDX', 'cityName' => 'Bordeaux'],
            ],
        ]);

        $this->assertSame(['BDX', 'CDG'], $valueView->vars['template_vars']['labels']);
    }

    public function testItemsAreGroupedByDottedGroupByPath()
    {
        $column = $this->createColumn([
            'property_path' => '[data]',
            'item_formatter' => static fn (array $item) => $item['code'],
            'group_by' => 'country.name',
        ]);

        $valueView = $this->createColumnValueView($column, rowData: [
            'data' => [
                ['code' => 'CDG', 'country' => ['name' => 'France']],
                ['code' => 'BDX', 'country' => ['name' => 'France']],
                ['code' => 'JFK', 'country' => ['name' => 'USA']],
            ],
        ]);

        $this->assertSame(
            ['France' => ['CDG', 'BDX'], 'USA' => ['JFK']],
            $valueView->vars['template_vars']['grouped_labels']
        );
    }

    public function testItemsMissingGroupByPathFallBackToOtherGroup()
    {
        $column = $this->createColumn([
            'property_path' => '[data]',
            'item_formatter' => static fn (array $item) => $item['code'],
            'group_by' => 'country.name',
        ]);

        $valueView = $this->createColumnValueView($column, rowData: [
            'data' => [
                ['code' => 'CDG', 'country' => ['name' => 'France']],
                ['code' => 'JRS', 'country' => null],
            ],
        ]);

        $this->assertSame(
            ['France' => ['CDG'], 'Other' => ['JRS']],
            $valueView->vars['template_vars']['grouped_labels']
        );
    }

    public function testOtherGroupIsAlwaysSortedLast()
    {
        $column = $this->createColumn([
            'property_path' => '[data]',
            'item_formatter' => static fn (array $item) => $item['code'],
            'group_by' => 'country.name',
        ]);

        $valueView = $this->createColumnValueView($column, rowData: [
            'data' => [
                ['code' => 'JRS', 'country' => null],
                ['code' => 'JFK', 'country' => ['name' => 'USA']],
                ['code' => 'CDG', 'country' => ['name' => 'France']],
            ],
        ]);

        $this->assertSame(
            ['France', 'USA', 'Other'],
            array_keys($valueView->vars['template_vars']['grouped_labels'])
        );
    }

    public function testDefaultOptionsAreUsedWhenNotSpecified()
    {
        $column = $this->createColumn([
            'property_path' => '[data]',
            'item_formatter' => static fn (array $item) => $item['code'],
        ]);

        $valueView = $this->createColumnValueView($column, rowData: [
            'data' => [
                ['code' => 'CDG'],
            ],
        ]);

        $this->assertSame(8, $valueView->vars['template_vars']['limit']);
        $this->assertSame(', ', $valueView->vars['template_vars']['separator']);
        $this->assertSame('components/LongList.html.twig', $valueView->vars['template_path']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new LongListColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }
}
