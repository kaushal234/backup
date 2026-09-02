<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;

abstract class AbstractApiFilterType extends AbstractFilterType
{
    public function getParent(): ?string
    {
        return ApiFilterType::class;
    }
}
