<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Type;

use App\DataTable\Filter\Type\ApiFilterType;
use App\DataTable\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterType;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;

/**
 * @group unit
 */
class TextFilterTypeTest extends FilterTypeTestCase
{
    public function test(): void
    {
        $filter = $this->createFilter();

        $this->assertSame('text', $filter->getName());
    }

    protected function getTestedType(): string
    {
        return TextFilterType::class;
    }

    /**
     * @return array<int, mixed> $options
     */
    protected function getTypes(): array
    {
        return [
            new TextFilterType(),
            new ApiFilterType(),
            new FilterType(),
        ];
    }
}
