<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Iri\Iri;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\AsmFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\CompetitorFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductTypeFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForecastClosureDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'sales/forecast_closures';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', ColumnType::class, [
                'label' => 'sidebar.sales.fcr',
                'header_translation_domain' => 'sidebar',
                'sort' => true,
            ])
            ->addColumn('salesForecast', TextColumnType::class, [
                'label' => 'sidebar.sales.sfr',
                'header_translation_domain' => 'sidebar',
                'formatter' => static function (array $salesForecast) {
                    return Iri::id($salesForecast);
                },
                'sort' => 'salesForecast.id',
            ])
            ->addColumn('asm', PeopleColumnType::class, [
                'label' => 'sales_forecasts.fields.asm',
                'header_translation_domain' => 'sales_forecasts',
                'property_path' => '[salesForecast][asm]',
                'sort' => 'salesForecast.asm.lastname',
            ])
            ->addColumn('factory', LocationColumnType::class, [
                'label' => 'fields.factory',
                'header_translation_domain' => 'messages',
                'property_path' => '[salesForecast?][factory?]',
                'sort' => 'salesForecast.factory.name',
            ])
            ->addColumn('sso', LocationColumnType::class, [
                'label' => 'fields.sso',
                'property_path' => '[salesForecast?][sso?]',
                'header_translation_domain' => 'messages',
                'sort' => 'salesForecast.sso.name',
            ])
            ->addColumn('buyer', TextColumnType::class, [
                'label' => 'fields.buyer',
                'header_translation_domain' => 'messages',
                'property_path' => '[salesForecast?][buyer?][name]',
                'sort' => 'salesForecast.buyer.name',
            ])
            ->addColumn('endUser', TextColumnType::class, [
                'label' => 'fields.end_user',
                'header_translation_domain' => 'messages',
                'property_path' => '[salesForecast?][endUser?][name]',
                'sort' => 'salesForecast.endUser.name',
            ])
            ->addColumn('winning', TextColumnType::class, [
                'label' => 'forecast_closures.fields.winning_party',
                'property_path' => '[competitor?][name]',
                'header_translation_domain' => 'forecast_closures',
                'sort' => 'competitor.name',
            ])
            ->addColumn('model', TextColumnType::class, [
                'label' => 'sales_forecasts.fields.product',
                'property_path' => '[salesForecast?][product?][name]',
                'header_translation_domain' => 'sales_forecasts',
                'sort' => 'salesForecast.product.name',
            ])
            ->addColumn('quantity', TextColumnType::class, [
                'label' => 'forecast_closures.fields.sfr_quantity',
                'property_path' => '[salesForecast?][quantity]',
                'header_translation_domain' => 'forecast_closures',
                'sort' => 'salesForecast.quantity',
            ])
            ->addColumn('orderedQuantity', TextColumnType::class, [
                'label' => 'forecast_closures.fields.quantity',
                'header_translation_domain' => 'forecast_closures',
                'sort' => true,
            ])
            ->addColumn('estimatedSaleDate', DateTimeColumnType::class, [
                'property_path' => '[salesForecast?][estimatedSaleDate]',
                'label' => 'forecast_closures.fields.sfr_date',
                'format' => 'Y-m',
                'header_translation_domain' => 'forecast_closures',
                'sort' => 'salesForecast.estimatedSaleDate',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'forecast_closures.fields.status',
                'header_translation_domain' => 'forecast_closures',
                'sort' => true,
            ])
            ->addColumn('reason', TextColumnType::class, [
                'label' => 'forecast_closures.fields.reason',
                'header_translation_domain' => 'forecast_closures',
                'sort' => true,
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('productType', TextColumnType::class, [
                'label' => 'catalogue.family.type',
                'header_translation_domain' => 'catalogue',
                'property_path' => '[salesForecast?][product?][family?][productType?][englishName]',
                'visible' => false,
            ])
        ;

        $builder
            ->addFilter('competitor', CompetitorFilterType::class, [
                'label' => 'forecast_closures.fields.winning_party',
                'translation_domain' => 'forecast_closures',
            ])
            ->addFilter('product', ProductFilterType::class, [
                'label' => 'sales_forecasts.fields.product',
                'translation_domain' => 'sales_forecasts',
                'query_path' => 'salesForecast.product',
            ])
            ->addFilter('productType', ProductTypeFilterType::class, [
                'label' => 'catalogue.family.type',
                'translation_domain' => 'catalogue',
                'query_path' => 'salesForecast.product.family.productType',
            ])
            ->addFilter('status', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'PARTIAL-ORDERED' => 'PARTIAL-ORDERED',
                        'PARTIAL-LOST' => 'PARTIAL-LOST',
                        'PARTIAL-*' => 'PARTIAL',
                        'ORDERED' => 'ORDERED',
                        'LOST' => 'LOST',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('reason', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'Technical / Equipment Performance' => 'PERFORMANCE',
                        'Service & Spare Part Support' => 'SUPPORT',
                        'Sales Job' => 'SALES',
                        'Requirement Cancelled' => 'CANCELLED',
                        'Price' => 'PRICE',
                        'Payment Terms' => 'PAYMENT',
                        'Lead-Time' => 'LEAD_TIME',
                        'Customer Loyalty' => 'LOYALTY',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('asm', AsmFilterType::class, [
                'label' => 'customers.fields.asm',
                'translation_domain' => 'sales_customers',
                'query_path' => 'salesForecast.asm',
            ])
            ->addFilter('factory', LocationFilterType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'query_path' => 'salesForecast.factory',
            ])
            ->addFilter('sso', LocationFilterType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'query_path' => 'salesForecast.sso',
            ])
            ->addFilter('country', CountryFilterType::class, [
                'label' => 'settings.address.form.country.label',
                'translation_domain' => 'messages',
                'query_path' => 'salesForecast.buyer.country',
            ])
            ->addFilter('buyer', CustomerFilterType::class, [
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
                'query_path' => 'salesForecast.buyer',
            ])
            ->addFilter('endUser', CustomerFilterType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
                'query_path' => 'salesForecast.endUser',
            ])
            ->addFilter('estimatedSaleDate', DateRangeFilterType::class, [
                'label' => 'forecast_closures.fields.sfr_date',
                'translation_domain' => 'forecast_closures',
                'query_path' => 'salesForecast.estimatedSaleDate',
            ])
        ;

        $builder
            // Simple search top right, using 'q' parameter of ApiPlatform
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'forecast_closures_show',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'forecast_closures.title',
            'translation_domain' => 'forecast_closures',
        ]);
    }
}
