<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\CustomerColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\JuridicalLocation\JuridicalLocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Location\SsoFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\AsmFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'sales/orders';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'header_translation_domain' => 'messages',
                'route' => 'sales_orders_show',
                'sort' => true,
            ])
            ->addColumn('equoteId', TextColumnType::class, [
                'label' => 'sales_order.fields.equote_id',
                'sort' => true,
            ])
            ->addColumn('enteredAt', DateTimeColumnType::class, [
                'label' => 'sales_order.fields.entered_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('sso', LocationColumnType::class, [
                'label' => 'fields.sso',
                'header_translation_domain' => 'messages',
                'sort' => 'sso.name',
            ])
            ->addColumn('juridicalLocation', TextColumnType::class, [
                'label' => 'directory.juridical_location.name',
                'header_translation_domain' => 'directory',
                'property_path' => '[juridicalLocation?][name]',
                'sort' => 'juridicalLocation.name',
            ])
            ->addColumn('asm', PeopleColumnType::class, [
                'label' => 'sales.sales_areas.asm',
                'header_translation_domain' => 'sales',
                'sort' => 'asm.lastname',
            ])
            ->addColumn('buyer', CustomerColumnType::class, [
                'label' => 'fields.buyer',
                'header_translation_domain' => 'messages',
                'property_path' => '[buyer]',
            ])
            ->addColumn('endUser', CustomerColumnType::class, [
                'label' => 'fields.end_user',
                'header_translation_domain' => 'messages',
                'property_path' => '[endUser]',
            ])
            ->addColumn('salesAgent', CustomerColumnType::class, [
                'label' => 'sales_order.fields.sales_agent',
                'header_translation_domain' => 'sales_orders',
                'property_path' => '[salesAgent]',
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'sales_orders_show',
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'label' => '',
                'href' => fn (ApiData $order): string => $this->urlGenerator->generate('sales_orders_edit', [
                    'id' => $order->getIriId(),
                ]),
                'icon' => 'fa7-solid:pencil',
                'variant' => 'warning',
            ])
        ;

        $builder
            ->addFilter('sso', SsoFilterType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
            ])
            ->addFilter('juridicalLocation', JuridicalLocationFilterType::class, [
                'label' => 'directory.juridical_location.name',
                'translation_domain' => 'directory',
            ])
            ->addFilter('baanCustomerNumber', TextFilterType::class, [
                'label' => 'spq.quotations.fields.cuno',
                'translation_domain' => 'spq',
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'fields.status',
                'translation_domain' => 'messages',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'PENDING' => 'PENDING',
                        'IN PROGRESS' => 'IN PROGRESS',
                        'CLOSED' => 'CLOSED',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('equoteId', TextFilterType::class, [
                'label' => 'sales_forecasts.fields.equote',
                'translation_domain' => 'sales_forecasts',
            ])
            ->addFilter('baanOrderNumbers', TextFilterType::class, [
                'label' => 'sales_order.fields.baan_order_number',
                'translation_domain' => 'sales_orders',
            ])
            ->addFilter('asm', AsmFilterType::class, [
                'label' => 'customers.fields.asm',
                'translation_domain' => 'sales_customers',
            ])
            ->addFilter('buyer', CustomerFilterType::class, [
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
            ])
            ->addFilter('endUser', CustomerFilterType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
            ])
            ->addFilter('salesAgent', CustomerFilterType::class, [
                'label' => 'sales_order.fields.sales_agent',
                'translation_domain' => 'sales_orders',
            ])
            ->addFilter('newCustomer', TextFilterType::class, [
                'label' => 'sales_order.fields.new_customer',
                'translation_domain' => 'sales_orders',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'Yes' => 1,
                        'No' => 0,
                    ],
                ],
            ])
            ->addFilter('customerPurchaseOrders', TextFilterType::class, [
                'label' => 'sales_order.fields.customer_purchase_orders',
                'translation_domain' => 'sales_orders',
            ])
            ->addFilter('enteredAt', DateRangeFilterType::class, [
                'label' => 'sales_order.fields.entered_at',
                'translation_domain' => 'sales_orders',
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search): void {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'enteredAt' => 'desc',
            ]))
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,status,enteredAt,equoteId,sso.name,juridicalLocation.name,asm,buyer.name,endUser.name,salesAgent.name,baanCustomerNumber,baanOrderNumbers,customerPurchaseOrders,newCustomer,note',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'sales_order.title',
            'translation_domain' => 'sales_orders',
        ]);
    }
}
