<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use AppBundle\DataTable\Filter\Handler\ApiFilterHandler;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;

final class ApiFilterType extends AbstractFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler(new ApiFilterHandler());
    }
}
