<?php

declare(strict_types=1);

namespace App\DataTable\Type\Equipment;

use App\DataTable\Action\Type\ShowButtonActionType;
use App\DataTable\Filter\Type\AirportFilterType;
use App\DataTable\Filter\Type\CountryFilterType;
use App\DataTable\Filter\Type\EquipmentRecordFilterType;
use App\DataTable\Filter\Type\ProductFilterType;
use App\DataTable\Filter\Type\ProductTypeFilterType;
use App\DataTable\Query\ApiProxyQuery;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\Customer;
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
class EquipmentRecordDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('serialNumber', TextColumnType::class, [
                'label' => 'calibration_tools.tools.fields.serialNumber',
                'sort' => true,
            ])
            ->addColumn('customerSerialNumber', TextColumnType::class, [
                'label' => 'extranet.fields.customer_asset_number',
                'sort' => true,
            ])
            ->addColumn('productType', TextColumnType::class, [
                'label' => 'display.table.vwc.headers.type',
                'sort' => 'product.family.productType.englishName',
            ])
            ->addColumn('customer', TextColumnType::class, [
                'label' => 'display.table.vwc_wc.header.customer',
                'sort' => 'endUser.name',
                'formatter' => static function (Customer $customer): string {
                    return $customer->name;
                },
            ])
            ->addColumn('product', TextColumnType::class, [
                'label' => 'display.table.vwc_wc.header.model',
                'sort' => 'product.name',
            ])
            ->addColumn('airport', TextColumnType::class, [
                'label' => 'fields.airport',
                'sort' => 'airport.code',
                'formatter' => static function (Airport $airport): string {
                    return $airport->code;
                },
            ])
            ->addColumn('country', TextColumnType::class, [
                'label' => 'menu.country.title',
                'sort' => true,
                'property_path' => 'airport?.country?.name',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->setDefaultSortingData(SortingData::fromArray(['serialNumber' => 'desc']));
        $builder->setDefaultPaginationData(new PaginationData(page: 1, perPage: 10));

        $builder
            ->addFilter('serialNumber', EquipmentRecordFilterType::class, [
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('country', CountryFilterType::class, [
                'label' => 'menu.country.title',
                'query_path' => 'airport.country.name',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('productType', ProductTypeFilterType::class, [
                'query_path' => 'product.family.productType',
                'form_options' => [
                    'multiple' => true,
                    'autocomplete' => true,
                ],
            ])
            ->addFilter('product', ProductFilterType::class, [
                'label' => 'display.table.vwc_wc.header.model',
                'query_path' => 'product.name',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('airport', AirportFilterType::class, [
                'query_path' => 'airport.code',
                'form_options' => [
                    'multiple' => true,
                ],
            ]
            );

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'equipment:show',
        ]);
    }

    public function buildView(DataTableView $view, DataTableInterface $dataTable, array $options): void
    {
        parent::buildView($view, $dataTable, $options);
        $view->vars['search_placeholder'] = $options['search_placeholder'];
        $view->vars['count_label'] = $options['count_label'];
        $view->vars['empty_state_title'] = $options['empty_state_title'];
        $view->vars['empty_state_link'] = $options['empty_state_link'];
        $view->vars['empty_state_route'] = $options['empty_state_route'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'Equipments',
            'translation_domain' => 'messages',
            'search_placeholder' => 'extranet.equipment.search_placeholder',
            'count_label' => 'equipments',
            'empty_state_title' => 'extranet.equipment.empty_state.title',
            'empty_state_link' => 'extranet.equipment.empty_state.link',
            'empty_state_route' => 'contact:index',
        ]);
    }
}
