<?php

declare(strict_types=1);

namespace App\Tests\ExternalERP\Filter;

use App\ExternalERP\Filter\ContainsFilter;

final class ContainsFilterTest extends AbstractFilterTest
{
    protected function getFilterClass(): string
    {
        return ContainsFilter::class;
    }

    protected function getFilterConstant(): string
    {
        return ContainsFilter::FILTER_PROPERTY;
    }

    protected function getDescriptionText(): string
    {
        return 'Contains filter';
    }
}
