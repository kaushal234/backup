<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Type;

use App\DataTable\Filter\Handler\ApiFilterHandler;
use App\DataTable\Filter\Type\ApiFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

/**
 * @group unit
 */
class ApiFilterTypeTest extends FilterTypeTestCase
{
    public function test(): void
    {
        $filter = $this->createFilter();

        $this->assertInstanceOf(ApiFilterHandler::class, $filter->getConfig()->getHandler());
    }

    protected function getTestedType(): string
    {
        return ApiFilterType::class;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getTypes(): array
    {
        return [
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
