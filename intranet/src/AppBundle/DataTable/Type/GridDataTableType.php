<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type;

use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple DataTable is principaly used to create light datatable.
 * For exemple, if you want to display a datatable inside a tab or a little bootstrap card.
 * By default, we disabled all functionnalities to really have a simple display.
 */
final class GridDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder->setDefaultPaginationData(new PaginationData(
            page: 1,
            perPage: 12,
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'personalization_enabled' => false,
            'themes' => [
                'datatable_grid_theme.html.twig',
            ],
        ]);
    }
}
