<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Support;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\FactoryFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFamilyFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManualDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'support/manuals';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'sort' => true,
                'route' => 'manuals_show',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'customers.fields.status',
                'header_translation_domain' => 'sales_customers',
            ])
            ->addColumn('language', TextColumnType::class, [
                'label' => 'contacts.fields.language',
                'header_translation_domain' => 'contacts',
            ])
            ->addColumn('createdBy', PeopleTooltipColumnType::class, [
                'label' => 'fields.poster',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('equipmentRecord', TextColumnType::class, [
                'label' => 'service.equipment_record.title',
                'header_translation_domain' => 'service',
                'property_path' => '[equipmentRecord?][serialNumber]',
            ])
            ->addColumn('equipmentSerial', TextColumnType::class, [
                'label' => 'print.equipmentSerial',
                'header_translation_domain' => 'emails',
                'property_path' => '[equipmentSerial?][serial]',
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'manuals_show',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('createdBy', PeopleFilterType::class, [
                'label' => 'non_conformity.fields.reported_by',
                'translation_domain' => 'non_conformity',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'sales_forecasts.fields.status',
                'translation_domain' => 'sales_forecasts',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'RELEASED' => 'RELEASED',
                        'PRELIMINARY' => 'PRELIMINARY',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('productFamilyName', ProductFamilyFilterType::class, [
                'label' => 'catalogue.family.product_family',
                'translation_domain' => 'catalogue',
                'form_options' => ['multiple' => true],
                'query_path' => 'equipmentRecord.product.family',
            ])
            ->addFilter('factory', FactoryFilterType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'query_path' => 'equipmentRecord.manufacturerLocation',
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'support.title',
            'translation_domain' => 'support',
        ]);
    }
}
