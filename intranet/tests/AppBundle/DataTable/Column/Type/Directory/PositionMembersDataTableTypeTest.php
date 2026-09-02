<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\Directory;

use AppBundle\DataTable\Type\Directory\PositionMembersDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PositionMembersDataTableTypeTest extends KernelTestCase
{
    public function testItDeclaresTheNameEmailAndBusinessUnitColumns(): void
    {
        $dataTable = $this->createDataTable();

        self::assertTrue($dataTable->hasColumn('name'));
        self::assertTrue($dataTable->hasColumn('email'));
        self::assertTrue($dataTable->hasColumn('businessUnit'));
        self::assertCount(3, $dataTable->getColumns());
    }

    public function testTheNameColumnSortsByLastname(): void
    {
        $dataTable = $this->createDataTable();

        $column = $dataTable->getColumn('name');

        self::assertTrue($column->getConfig()->isSortable());
        self::assertSame('lastname', (string) $column->getSortPropertyPath());
    }

    public function testTheEmailColumnIsNotSortable(): void
    {
        $dataTable = $this->createDataTable();

        self::assertFalse($dataTable->getColumn('email')->getConfig()->isSortable());
    }

    public function testTheBusinessUnitColumnSortsByBusinessUnitName(): void
    {
        $dataTable = $this->createDataTable();

        $column = $dataTable->getColumn('businessUnit');

        self::assertTrue($column->getConfig()->isSortable());
        self::assertSame('businessUnit.name', (string) $column->getSortPropertyPath());
    }

    public function testTheDefaultSortIsByNameAscending(): void
    {
        $dataTable = $this->createDataTable();

        $sortingData = $dataTable->getConfig()->getDefaultSortingData();
        $nameColumn = $sortingData->getColumn('name');

        self::assertNotNull($nameColumn);
        self::assertSame('name', $nameColumn->getName());
        self::assertSame('asc', $nameColumn->getDirection());
    }

    public function testItDeclaresOnlyTheAutoAddedSearchFilter(): void
    {
        $dataTable = $this->createDataTable();

        self::assertSame(['__search'], array_keys($dataTable->getFilters()));
    }

    private function createDataTable(): \Kreyu\Bundle\DataTableBundle\DataTableInterface
    {
        self::bootKernel();

        return self::getContainer()
            ->get(DataTableFactoryInterface::class)
            ->create(
                PositionMembersDataTableType::class,
                PositionMembersDataTableType::RESOURCE,
                ['title' => 'Members of TEST'],
            );
    }
}
