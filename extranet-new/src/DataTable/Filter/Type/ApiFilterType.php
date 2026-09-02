<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\DataTable\Filter\Handler\ApiFilterHandler;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'kreyu_data_table.filter.type')]
final class ApiFilterType extends AbstractFilterType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler(new ApiFilterHandler());
    }
}
