<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Type;

use AppBundle\DataTable\Filter\Type\ApiFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class DateRangeFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();
        $this->assertSame('date_range', $filter->getName());
    }

    protected function getTestedType(): string
    {
        return DateRangeFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new DateRangeFilterType(),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
