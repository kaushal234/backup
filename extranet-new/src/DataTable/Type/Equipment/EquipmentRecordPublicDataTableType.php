<?php

declare(strict_types=1);

namespace App\DataTable\Type\Equipment;

use App\DataTable\Action\Type\ShowButtonActionType;
use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AutoconfigureTag(name: 'kreyu_data_table.type')]
class EquipmentRecordPublicDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('serialNumber', TextColumnType::class, [
                'label' => 'calibration_tools.tools.fields.serialNumber',
                'sort' => true,
            ])
            ->addColumn('product', TextColumnType::class, [
                'label' => 'display.table.vwc_wc.header.model',
                'sort' => 'model',
            ])
            ->addColumn('productType', TextColumnType::class, [
                'label' => 'display.table.vwc.headers.type',
                'sort' => 'type',
            ])
            ->addColumn('airportCode', TextColumnType::class, [
                'label' => 'fields.airport',
                'sort' => 'airport.code',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->setDefaultSortingData(SortingData::fromArray(['serialNumber' => 'desc']));
        $builder->setDefaultPaginationData(new PaginationData(page: 1, perPage: 10));

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'equipment:show:public',
            'key' => 'serialNumber',
            'property_path_link' => 'serialNumber',
        ]);
    }

    public function buildView(DataTableView $view, DataTableInterface $dataTable, array $options): void
    {
        parent::buildView($view, $dataTable, $options);
        $view->vars['search_placeholder'] = $options['search_placeholder'];
        $view->vars['count_label'] = $options['count_label'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'Equipments',
            'translation_domain' => 'messages',
            'search_placeholder' => 'extranet.equipment.search_placeholder',
            'count_label' => 'equipments',
            // Public page: no authenticated user, so per-user persistence (which relies on the
            // token storage as the persistence subject) cannot be used.
            'sorting_persistence_enabled' => false,
            'filtration_persistence_enabled' => false,
            'pagination_persistence_enabled' => false,
        ]);
    }
}
