<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use ApiBundle\Client;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class AutocompleteFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();
        $this->assertSame('filter', $filter->getName());
    }

    protected function getTestedType(): string
    {
        return AutocompleteFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new AutocompleteFilterType($this->createMock(Client::class)),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
