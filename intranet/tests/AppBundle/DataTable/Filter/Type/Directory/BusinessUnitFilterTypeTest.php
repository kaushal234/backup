<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use ApiBundle\Client;
use AppBundle\DataTable\Filter\Type\ApiFilterType;
use AppBundle\DataTable\Filter\Type\AutocompleteFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class BusinessUnitFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();
        $view = $this->createFilterView($filter, new FilterData(['name' => 'My BusinessUnit']));
        $this->assertSame('business_unit', $filter->getName());
        $this->assertSame('My BusinessUnit', $view->value);
    }

    protected function getTestedType(): string
    {
        return BusinessUnitFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new BusinessUnitFilterType(),
            new AutocompleteFilterType($this->createMock(Client::class)),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
