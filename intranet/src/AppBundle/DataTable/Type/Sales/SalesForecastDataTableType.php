<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\AirportColumnType;
use AppBundle\DataTable\Column\Type\CountryColumnType;
use AppBundle\DataTable\Column\Type\CustomerColumnType;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Column\Type\ProgressBarColumnType;
use AppBundle\DataTable\Column\Type\Sales\Catalog\ProductColumnType;
use AppBundle\DataTable\Column\Type\Support\EmissionRatingColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\AsmFilterType;
use AppBundle\DataTable\Filter\Type\Finance\FinanceFamilyFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFamilyFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductTypeFilterType;
use AppBundle\DataTable\Filter\Type\Support\EmissionRatingFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'sales/sales_forecasts';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'sales_forecasts.fields.id',
                'header_translation_domain' => 'sales_forecasts',
                'route' => 'sales_forecasts_show',
                'sort' => true,
            ])
            ->addColumn('legacyId', TextColumnType::class, [
                'label' => 'fields.legacy_id',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('asm', PeopleTooltipColumnType::class, [
                'label' => 'sales_forecasts.fields.asm',
                'header_translation_domain' => 'sales_forecasts',
                'sort' => 'asm.lastname',
            ])
            ->addColumn('factory', LocationColumnType::class, [
                'label' => 'fields.factory',
                'header_translation_domain' => 'messages',
                'sort' => 'factory.name',
            ])
            ->addColumn('sso', LocationColumnType::class, [
                'label' => 'fields.sso',
                'header_translation_domain' => 'messages',
                'sort' => 'sso.name',
            ])
            ->addColumn('buyer', CustomerColumnType::class, [
                'label' => 'fields.buyer',
                'header_translation_domain' => 'messages',
                'sort' => 'buyer.name',
            ])
            ->addColumn('endUser', CustomerColumnType::class, [
                'label' => 'fields.end_user',
                'header_translation_domain' => 'messages',
                'sort' => 'endUser.name',
            ])
            ->addColumn('thirdParty', CustomerColumnType::class, [
                'label' => 'sales_forecasts.fields.third_party',
                'header_translation_domain' => 'sales_forecasts',
                'sort' => 'thirdParty.name',
            ])
            ->addColumn('product', ProductColumnType::class, [
                'label' => 'sales_forecasts.fields.product',
                'header_translation_domain' => 'sales_forecasts',
                'sort' => 'product.name',
            ])
            ->addColumn('quantity', TextColumnType::class, [
                'label' => 'fields.quantity',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'header_translation_domain' => 'messages',
                'formatter' => static fn (string $status) => str_replace('_', ' ', $status),
                'sort' => true,
            ])
            ->addColumn('airport', AirportColumnType::class, [
                'sort' => 'airport.name',
                'visible' => false,
            ])
            ->addColumn('country', CountryColumnType::class, [
                'sort' => 'country.name',
                'visible' => false,
            ])
            ->addColumn('tier', EmissionRatingColumnType::class, [
                'sort' => 'tier.name',
                'visible' => false,
            ])
            ->addColumn('delinquent', BooleanColumnType::class, [
                'label' => 'sales_forecasts.fields.delinquent',
                'header_translation_domain' => 'sales_forecasts',
                'sort' => true,
                'visible' => false,
            ])
            ->addColumn('estimatedSaleDate', DateColumnType::class, [
                'label' => 'fields.estimatedSaleDate',
                'header_translation_domain' => 'messages',
                'sort' => true,
                'visible' => true,
            ])
            ->addColumn('successPercentage', ProgressBarColumnType::class, [
                'label' => 'fields.successPercentage',
                'header_translation_domain' => 'messages',
                'sort' => true,
                'visible' => true,
            ])
            ->addColumn('logs', TemplateColumnType::class, [
                'label' => 'activity.log.name',
                'header_translation_domain' => 'messages',
                'template_path' => 'sales/sales_forecasts/partial/cells/_activity.html.twig',
                'getter' => static fn (ApiData|array $salesForecast) => $salesForecast,
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'sales_forecasts_show',
            ])
        ;

        $builder
            ->addFilter('buyer', CustomerFilterType::class, [
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('endUser', CustomerFilterType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('thirdParty', CustomerFilterType::class, [
                'label' => 'sales_forecasts.fields.third_party',
                'translation_domain' => 'sales_forecasts',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('closedAt', DateRangeFilterType::class, [
                'label' => 'fields.closed_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('factory', LocationFilterType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('sso', LocationFilterType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('airport', AirportFilterType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'sales_forecasts.fields.status',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'BUDGET' => 'BUDGET',
                        'IN_PROGRESS' => 'IN_PROGRESS',
                        'DELAYED' => 'DELAYED',
                        'ORDERED' => 'ORDERED',
                        'LOST' => 'LOST',
                        'CANCELLED' => 'CANCELLED',
                        'PARTIAL' => 'PARTIAL',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('asm', AsmFilterType::class, [
                'label' => 'sales_forecasts.fields.asm',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('country', CountryFilterType::class, [
                'label' => 'settings.address.form.country.label',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('product', ProductFilterType::class, [
                'label' => 'sales_forecasts.fields.product',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('productFamily', ProductFamilyFilterType::class, [
                'label' => 'catalogue.family.product_family',
                'translation_domain' => 'catalogue',
                'query_path' => 'product.family',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('productType', ProductTypeFilterType::class, [
                'label' => 'catalogue.family.type',
                'translation_domain' => 'catalogue',
                'query_path' => 'product.family.productType',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('financeFamily', FinanceFamilyFilterType::class, [
                'label' => 'catalogue.products.finance_family',
                'translation_domain' => 'catalogue',
                'query_path' => 'product.financeFamily',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('tier', EmissionRatingFilterType::class, [
                'label' => 'sales_forecasts.fields.tier',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('delinquent', BooleanFilterType::class, [
                'label' => 'sales_forecasts.fields.delinquent',
            ])
            ->addFilter('open', BooleanFilterType::class, [
                'label' => 'sales_forecasts.fields.open',
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,createdAt,lastCommentedAt,status,lastComment,sso,sso.currency,factory,asm,buyer,endUser,airport,product,quantity,margin,price,estimatedSaleDate,customerSuccessPercentage,successPercentage,totalSuccessPercentage,country,tier,equoteId',
                ],
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'sales_forecasts.title',
            'translation_domain' => 'sales_forecasts',
        ]);
    }
}
