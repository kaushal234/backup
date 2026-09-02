<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Parts\SparePartsRequest;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\AirportColumnType;
use AppBundle\DataTable\Column\Type\CustomerColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\SparePartsRequestParentColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Parts\SparePartRequestDeliveryAddressFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SparePartsRequestDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'route' => 'spare_parts_request_show',
                'sort' => true,
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'header_translation_domain' => 'messages',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('status', LabelColumnType::class, [
                'label' => 'fields.status',
                'header_translation_domain' => 'messages',
                'label_classes' => [
                    'PENDING' => 'warning',
                    'OPEN' => 'info',
                    'SHIPPED' => 'primary',
                    'CLOSED' => 'success',
                ],
                'sort' => true,
            ])
            ->addColumn('sph', LocationColumnType::class, [
                'label' => 'spq.quotations.fields.sph',
                'header_translation_domain' => 'spq',
                'sort' => 'sph.name',
            ])
            ->addColumn('factory', LocationColumnType::class, [
                'label' => 'fields.factory',
                'header_translation_domain' => 'messages',
                'sort' => 'factory.name',
            ])
            ->addColumn('airport', AirportColumnType::class, [
                'sort' => 'airport.code',
            ])
            ->addColumn('salesOrder', TextColumnType::class, [
                'label' => 'account_receivable.fields.sales_order_number',
                'header_translation_domain' => 'account_receivable',
                'sort' => true,
            ])
            ->addColumn('customer', CustomerColumnType::class, [
                'sort' => 'customer.name',
            ])
            ->addColumn('activity', TextColumnType::class, [
                'label' => 'spare_parts_request.fields.activity',
                'sort' => true,
            ])
            ->addColumn('type', LabelColumnType::class, [
                'label' => 'spare_parts_request.fields.type',
                'sort' => true,
            ])
            ->addColumn('parent', SparePartsRequestParentColumnType::class, [
                'getter' => static function ($data) {
                    return null;
                },
            ])
        ;

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'spare_parts_request_show',
        ]);

        $builder
            ->addFilter('sph', LocationFilterType::class, [
                'label' => 'directory.location_capability.fields.sparePartsHub',
                'translation_domain' => 'directory',
            ])
            ->addFilter('factory', LocationFilterType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
            ])
            ->addFilter('shippingOrigin', LocationFilterType::class, [
                'label' => 'spare_parts_request.fields.shipping_origin',
                'query_path' => 'parts.shippingOrigin',
            ])
            ->addFilter('poster', PeopleFilterType::class, [
                'label' => 'fields.poster',
                'translation_domain' => 'messages',
            ])
            ->addFilter('airport', AirportFilterType::class)
            ->addFilter('status', TextFilterType::class, [
                'label' => 'fields.status',
                'translation_domain' => 'messages',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'PENDING' => 'PENDING',
                        'OPEN' => 'OPEN',
                        'SHIPPED' => 'SHIPPED',
                        'CLOSED' => 'CLOSED',
                    ],
                ],
            ])
            ->addFilter('type', TextFilterType::class, [
                'label' => 'fields.type',
                'translation_domain' => 'messages',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'Not Defined Yet' => 'Not Defined Yet',
                        'Warranty' => 'Warranty',
                        'Payable Services' => 'Payable Services',
                        'Factory' => 'Factory',
                        'SSO' => 'SSO',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('shippingDate', DateRangeFilterType::class, [
                'label' => 'spare_parts_request.fields.shipping_date',
            ])
            ->addFilter('partNumber', TextFilterType::class, [
                'label' => 'spare_parts_request.fields.part_number',
                'query_path' => 'parts.partNumber',
            ])
            ->addFilter('salesOrder', TextFilterType::class, [
                'label' => 'account_receivable.fields.sales_order_number',
                'translation_domain' => 'account_receivable',
            ])
            ->addFilter('customer', CustomerFilterType::class)
            ->addFilter('deliveryAddress', SparePartRequestDeliveryAddressFilterType::class, [
                'label' => 'spare_parts_request.fields.delivery_address',
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,createdAt,status,sph.name,factory.name,airport.code,salesOrder,customer.name,activity,type,linkedModuleId',
                ],
            ]);

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'spare_parts_request.sprs',
            'translation_domain' => 'spare_parts_request',
        ]);
    }
}
