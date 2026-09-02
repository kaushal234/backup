<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use ApiBundle\Client;
use AppBundle\DataTable\Filter\Type\ApiFilterType;
use AppBundle\DataTable\Filter\Type\AutocompleteFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class LocationFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();
        $view = $this->createFilterView($filter, new FilterData(['name' => 'My location']));
        $this->assertSame('location', $filter->getName());
        $this->assertSame('My location', $view->value);
    }

    protected function getTestedType(): string
    {
        return LocationFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new LocationFilterType(),
            new AutocompleteFilterType($this->createMock(Client::class)),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
