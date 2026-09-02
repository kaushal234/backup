<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use ApiBundle\Client;
use AppBundle\DataTable\Filter\Type\ApiFilterType;
use AppBundle\DataTable\Filter\Type\AutocompleteFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Position\PositionFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class PositionFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();
        $view = $this->createFilterView($filter, new FilterData(['description' => 'My position']));
        $this->assertSame('position', $filter->getName());
        $this->assertSame('My position', $view->value);
    }

    protected function getTestedType(): string
    {
        return PositionFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new PositionFilterType(),
            new AutocompleteFilterType($this->createMock(Client::class)),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
