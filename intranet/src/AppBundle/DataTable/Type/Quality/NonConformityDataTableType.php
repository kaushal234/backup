<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Quality;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Column\Type\ResponsibleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DepartmentFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Quality\ProcessFilterType;
use AppBundle\DataTable\Filter\Type\Quality\ResponsibleFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NonConformityDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'quality/non_conformities';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'sort' => true,
                'route' => 'non_conformity_show',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'customers.fields.status',
                'header_translation_domain' => 'sales_customers',
            ])
            ->addColumn('rush', BooleanColumnType::class, [
                'label' => 'non_conformity.fields.rush',
                'header_translation_domain' => 'non_conformity',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('reportedBy', PeopleColumnType::class, [
                'label' => 'non_conformity.fields.reported_by',
                'header_translation_domain' => 'non_conformity',
                'sort' => 'reportedBy.lastname',
            ])
            ->addColumn('location', LocationColumnType::class, [
                'label' => 'directory.department.fields.location',
                'header_translation_domain' => 'directory',
            ])
            ->addColumn('responsibles', CollectionColumnType::class, [
                'label' => 'non_conformity.fields.responsible',
                'header_translation_domain' => 'non_conformity',
                'property_path' => 'responsibles',
                'entry_type' => ResponsibleColumnType::class,
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'competitors.fields.short_description',
                'header_translation_domain' => 'sales_competitors',
            ])
            ->addColumn('solution', TextColumnType::class, [
                'label' => 'non_conformity.fields.solution',
                'header_translation_domain' => 'non_conformity',
                'sort' => true,
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'non_conformity_show',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('reportedBy', PeopleFilterType::class, [
                'label' => 'non_conformity.fields.reported_by',
                'translation_domain' => 'non_conformity',
            ])
            ->addFilter('location', LocationFilterType::class, [
                'label' => 'fields.location',
                'translation_domain' => 'messages',
            ])
            ->addFilter('responsibles', ResponsibleFilterType::class, [
                'label' => 'non_conformity.fields.responsible',
                'translation_domain' => 'non_conformity',
            ])
            ->addFilter('products', ProductFilterType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'sales_forecasts.fields.status',
                'translation_domain' => 'sales_forecasts',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'PENDING' => 'PENDING',
                        'IN_PROGRESS' => 'IN PROGRESS',
                        'SUSPENDED' => 'SUSPENDED',
                        'REJECTED' => 'REJECTED',
                        'CLOSED' => 'CLOSED',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('processes', ProcessFilterType::class, [
                'label' => 'non_conformity.fields.process',
                'translation_domain' => 'non_conformity',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('department', DepartmentFilterType::class, [
                'label' => 'directory.department.name',
                'translation_domain' => 'directory',
                'query_path' => 'reportedBy.department',
            ])
            ->addFilter('supplierNumber', TextFilterType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_number',
                'translation_domain' => 'vendor_warranty_claim',
            ])
            ->addFilter('supplierName', TextFilterType::class, [
                'label' => 'finance.approver.supplier_name',
                'translation_domain' => 'finance',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('partNumber', TextFilterType::class, [
                'label' => 'spare_parts_request.fields.part_number',
                'translation_domain' => 'spare_parts_request',
                'query_path' => 'parts.partNumber',
            ])
            ->addFilter('serialNumber', TextFilterType::class, [
                'label' => 'calibration_tools.tools.fields.serialNumber',
                'translation_domain' => 'messages',
                'query_path' => 'parts.serialNumber',
            ])
            ->addFilter('serialNumberEquipmentRecord', TextFilterType::class, [
                'label' => 'demo.fields.er_serial_number',
                'translation_domain' => 'demo',
                'query_path' => 'equipmentRecords.serialNumber',
            ])
            ->addFilter('rush', BooleanFilterType::class, [
                'label' => 'non_conformity.fields.rush',
                'translation_domain' => 'non_conformity',
            ])
            ->addFilter('safety', BooleanFilterType::class, [
                'label' => 'non_conformity.fields.safety',
            ])
            ->addFilter('environmentalIssue', BooleanFilterType::class, [
                'label' => 'non_conformity.fields.environmental_issue',
            ])
            ->addFilter('partReferenceNumber', TextFilterType::class, [
                'label' => 'non_conformity.fields.part_reference_number',
                'translation_domain' => 'non_conformity',
                'query_path' => 'parts.referenceNumber',
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,status,location,createdAt,closedAt,turnAroundTime,reportedBy,shortDescription,problem,processes,investigation,responsibles,products,purchaseOrderNumber,partNumbers,quantity,partsDescription,serialNumbers,refTypes,references,rush,scrap,rework,useAsIs,derogation,returnVendor,chargeVendorForRepair,supplierCorrectiveActionRequest,other,actionComment,repairApprover,repairApprovalDate,solution,hours,currency,cost,costBreakdown,invoiceNumber,supplierNumber,supplierName,failureType,iFactor',
                ],
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'non_conformity.title.ncr',
            'translation_domain' => 'non_conformity',
        ]);
    }
}
