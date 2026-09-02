<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Type;

use AppBundle\DataTable\Filter\Type\ApiFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

class TextFilterTypeTest extends FilterTypeTestCase
{
    public function test()
    {
        $filter = $this->createFilter();

        $this->assertSame('text', $filter->getName());
    }

    protected function getTestedType(): string
    {
        return TextFilterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new TextFilterType(),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
