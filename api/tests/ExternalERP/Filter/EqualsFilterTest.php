<?php

declare(strict_types=1);

namespace App\Tests\ExternalERP\Filter;

use App\ExternalERP\Filter\EqualsFilter;

final class EqualsFilterTest extends AbstractFilterTest
{
    protected function getFilterClass(): string
    {
        return EqualsFilter::class;
    }

    protected function getFilterConstant(): string
    {
        return EqualsFilter::FILTER_PROPERTY;
    }

    protected function getDescriptionText(): string
    {
        return 'Equals filter';
    }
}
