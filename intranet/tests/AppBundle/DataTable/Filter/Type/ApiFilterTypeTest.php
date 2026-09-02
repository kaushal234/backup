<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Type;

use AppBundle\DataTable\Filter\Handler\ApiFilterHandler;
use AppBundle\DataTable\Filter\Type\ApiFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class ApiFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();

        $this->assertInstanceOf(ApiFilterHandler::class, $filter->getConfig()->getHandler());
    }

    protected function getTestedType(): string
    {
        return ApiFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
