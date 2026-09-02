<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Quality\SupplierCorrectiveActionRequest;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierCorrectiveActionRequestDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'quality/supplier_corrective_action_requests';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'ID',
                'route' => 'supplier_corrective_action_request_show',
                'sort' => true,
            ])
            ->addColumn('factory', LocationColumnType::class, [
                'label' => 'supplier_corrective_action_request.fields.location',
                'sort' => 'factory.name',
            ])
            ->addColumn('poster', PeopleColumnType::class, [
                'label' => 'fields.poster',
                'header_translation_domain' => 'messages',
                'sort' => 'poster.lastname',
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'supplier_corrective_action_request.fields.short_description',
                'sort' => true,
                'visible' => false,
            ])
            ->addColumn('representative', PeopleColumnType::class, [
                'label' => 'directory.location.fields.representative',
                'header_translation_domain' => 'directory',
                'visible' => false,
                'sort' => 'representative.lastname',
            ])
            ->addColumn('leader', PeopleColumnType::class, [
                'label' => 'supplier_corrective_action_request.fields.leader',
                'header_translation_domain' => 'supplier_corrective_action_request',
                'visible' => false,
                'sort' => 'leader.lastname',
            ])
            ->addColumn('supplierNumber', IdLinkColumnType::class, [
                'label' => 'fields.supplier.number',
                'header_translation_domain' => 'messages',
                'sort' => true,
                'route' => 'supplier_corrective_action_request_show',
            ])
            ->addColumn('supplierName', IdLinkColumnType::class, [
                'label' => 'finance.approver.supplier_name',
                'header_translation_domain' => 'finance',
                'sort' => true,
                'route' => 'supplier_corrective_action_request_show',
            ])
            ->addColumn('status', LabelColumnType::class, [
                'label' => 'sales_forecasts.fields.status',
                'header_translation_domain' => 'sales_forecasts',
                'label_classes' => [
                    'PENDING' => 'info',
                    'VENDOR TO FILL FORM' => 'default',
                    'TLD TO REVIEW FORM' => 'default',
                    'VALIDATION' => 'success',
                    'COMMERCIAL AGREEMENT' => 'success',
                    'CANCEL' => 'default',
                    'CLOSED' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('iFactor', LabelColumnType::class, [
                'label' => 'fields.ifactor',
                'header_translation_domain' => 'messages',
                'label_classes' => [
                    'IF1' => 'success',
                    'IF10' => 'info',
                    'IF100' => 'warning',
                    'IF1000' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'header_translation_domain' => 'messages',
                'format' => 'Y-m-d H:i',
                'sort' => true,
            ])
            ->addColumn('closedAt', DateTimeColumnType::class, [
                'label' => 'fields.closed_at',
                'header_translation_domain' => 'messages',
                'format' => 'Y-m-d H:i',
                'visible' => false,
                'sort' => true,
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'supplier_corrective_action_request_show',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('id', TextFilterType::class, [
                'label' => 'ID',
            ])
            ->addFilter('poster', PeopleFilterType::class, [
                'label' => 'fields.poster',
                'translation_domain' => 'messages',
            ])
            ->addFilter('leader', PeopleFilterType::class, [
                'label' => 'supplier_corrective_action_request.fields.leader',
                'translation_domain' => 'supplier_corrective_action_request',
            ])
            ->addFilter('representative', PeopleFilterType::class, [
                'label' => 'directory.location.fields.representative',
                'translation_domain' => 'directory',
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'sales_forecasts.fields.status',
                'translation_domain' => 'sales_forecasts',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'multiple' => true,
                    'choices' => [
                        'PENDING' => 'PENDING',
                        'VENDOR TO FILL FORM' => 'VENDOR TO FILL FORM',
                        'TLD TO REVIEW FORM' => 'TLD TO REVIEW FORM',
                        'VALIDATION' => 'VALIDATION',
                        'COMMERCIAL AGREEMENT' => 'COMMERCIAL AGREEMENT',
                        'CANCEL' => 'CANCEL',
                        'CLOSED' => 'CLOSED',
                    ],
                ],
            ])
            ->addFilter('iFactor', TextFilterType::class, [
                'label' => 'fields.ifactor',
                'translation_domain' => 'messages',
                'form_type' => ChoiceType::class,
                'form_options' => [
                    'choices' => [
                        'IF1' => 'IF1',
                        'IF10' => 'IF10',
                        'IF100' => 'IF100',
                        'IF1000' => 'IF1000',
                    ],
                ],
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('closedAt', DateRangeFilterType::class, [
                'label' => 'fields.closed_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('factory', LocationFilterType::class, [
                'label' => 'supplier_corrective_action_request.fields.location',
            ])
            ->addFilter('partNumber', TextFilterType::class, [
                'label' => 'supplier_corrective_action_request.fields.parts.part_numbers',
                'query_path' => 'parts.partNumber',
            ])
            ->addFilter('supplierNumber', TextFilterType::class, [
                'label' => 'supplier_corrective_action_request.fields.supplier_number',
            ])
            ->addFilter('supplierName', TextFilterType::class, [
                'label' => 'supplier_corrective_action_request.fields.supplier_name',
            ])
            ->addFilter('shortDescription', TextFilterType::class, [
                'label' => 'supplier_corrective_action_request.fields.short_description',
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,iFactor,factory.name,factory.erp,representative,poster,leader,status,supplierName,supplierNumber,createdAt,closedAt',
                ],
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'createdAt' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'supplier_corrective_action_request.supplier_corrective_action_request',
            'translation_domain' => 'supplier_corrective_action_request',
        ]);
    }
}
