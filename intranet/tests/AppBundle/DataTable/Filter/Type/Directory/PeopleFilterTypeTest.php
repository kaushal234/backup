<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use ApiBundle\Client;
use AppBundle\DataTable\Filter\Type\ApiFilterType;
use AppBundle\DataTable\Filter\Type\AutocompleteFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class PeopleFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();
        $view = $this->createFilterView($filter, new FilterData(['firstname' => 'John', 'lastname' => 'Doe']));
        $this->assertSame('people', $filter->getName());
        $this->assertSame('Doe John', $view->value);
    }

    protected function getTestedType(): string
    {
        return PeopleFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new PeopleFilterType(),
            new AutocompleteFilterType($this->createMock(Client::class)),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
