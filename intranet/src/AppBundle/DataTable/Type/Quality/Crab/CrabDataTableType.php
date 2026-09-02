<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Quality\Crab;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\FactoryFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Quality\Crab\CodeFilterType;
use AppBundle\DataTable\Filter\Type\Quality\Crab\DepartmentFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductTypeFilterType;
use AppBundle\DataTable\Filter\Type\Support\EquipmentRecordFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CrabDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'quality/crabs';

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
                'route' => 'crab_show',
                'sort' => true,
            ])
            ->addColumn('serialNumber', LinkColumnType::class, [
                'label' => 'calibration_tools.tools.fields.serialNumber',
                'header_translation_domain' => 'messages',
                'property_path' => '[equipmentRecord]',
                'sort' => 'equipmentRecord.serialNumber',
                'formatter' => static fn (?array $equipmentRecord = null): ?string => $equipmentRecord['serialNumber'] ?? null,
                'href' => function (?array $equipmentRecord = null): ?string {
                    if (null === $equipmentRecord || null === ($equipmentRecord['legacyId'] ?? null)) {
                        return null;
                    }

                    return $this->urlGenerator->generate('legacy_product_support', [
                        'id' => $equipmentRecord['legacyId'],
                        'm' => ['equipment', 'view'],
                    ]);
                },
            ])
            ->addColumn('type', TextColumnType::class, [
                'label' => 'crab.fields.type',
                'header_translation_domain' => 'crab',
                'property_path' => '[equipmentRecord][product?][family?][productType?][englishName]',
                'sort' => 'equipmentRecord.product.family.productType.englishName',
            ])
            ->addColumn('factory', LocationColumnType::class, [
                'label' => 'fields.factory',
                'header_translation_domain' => 'messages',
                'property_path' => '[equipmentRecord][manufacturerLocation]',
                'sort' => 'equipmentRecord.manufacturerLocation.name',
            ])
            ->addColumn('part', TextColumnType::class, [
                'label' => 'fields.part_number',
                'header_translation_domain' => 'messages',
                'property_path' => '[part?][partNumber]',
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'tasks.description',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('category', TextColumnType::class, [
                'label' => 'crab.fields.stage',
                'header_translation_domain' => 'crab',
                'sort' => true,
            ])
            ->addColumn('department', TextColumnType::class, [
                'label' => 'menu.department.title',
                'header_translation_domain' => 'messages',
                'property_path' => '[department?][name]',
                'sort' => 'department.name',
            ])
            ->addColumn('code', TextColumnType::class, [
                'label' => 'crab.fields.code',
                'header_translation_domain' => 'crab',
                'property_path' => '[code?][code]',
                'sort' => 'code.code',
            ])
            ->addColumn('codeDescription', TextColumnType::class, [
                'label' => 'crab.fields.code_description',
                'header_translation_domain' => 'crab',
                'property_path' => '[code?][description]',
                'visible' => false,
            ])
            ->addColumn('createdBy', PeopleTooltipColumnType::class, [
                'label' => 'fields.created_by',
                'header_translation_domain' => 'messages',
                'sort' => 'createdBy.lastname',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('fixedBy', PeopleTooltipColumnType::class, [
                'label' => 'crab.fields.fixed_by',
                'header_translation_domain' => 'crab',
                'sort' => 'fixedBy.lastname',
            ])
            ->addColumn('fixedAt', DateTimeColumnType::class, [
                'label' => 'crab.fields.fixed_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'crab',
                'sort' => true,
            ])
            ->addColumn('inspectedBy', PeopleTooltipColumnType::class, [
                'label' => 'crab.fields.inspected_by',
                'header_translation_domain' => 'crab',
                'sort' => 'inspectedBy.lastname',
            ])
            ->addColumn('inspectedAt', DateTimeColumnType::class, [
                'label' => 'crab.fields.inspected_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'crab',
                'sort' => true,
            ])
            ->addColumn('derogationStatus', TextColumnType::class, [
                'label' => 'crab.fields.derogation',
                'header_translation_domain' => 'crab',
                'property_path' => '[derogation?][status]',
            ])
        ;

        $builder
            ->addFilter('createdBy', PeopleFilterType::class, [
                'label' => 'fields.created_by',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('factory', FactoryFilterType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'query_path' => 'equipmentRecord.manufacturerLocation',
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'customers.fields.status',
                'translation_domain' => 'sales_customers',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'CLOSED' => 'CLOSED',
                        'TO-FIX' => 'TO-FIX',
                        'TO-INSPECT' => 'TO-INSPECT',
                        'FOR-DEROGATION' => 'FOR-DEROGATION',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('category', TextFilterType::class, [
                'label' => 'crab.fields.stage',
                'translation_domain' => 'crab',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'Assy' => 'Assy',
                        'Test' => 'Test',
                        'QA' => 'QA',
                        'PDI-CSC' => 'PDI-CSC',
                        'PDI-SOL' => 'PDI-SOL',
                        'PDI' => 'PDI',
                        'PDI-INTERNAL' => 'PDI-INTERNAL',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('piQuestionType', TextFilterType::class, [
                'label' => 'crab.fields.pi_questions_type',
                'translation_domain' => 'crab',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'ECQ' => 'ECQ',
                        'PCQ' => 'PCQ',
                        'QCQ' => 'QCQ',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('code', CodeFilterType::class, [
                'label' => 'crab.fields.code',
                'translation_domain' => 'crab',
            ])
            ->addFilter('department', DepartmentFilterType::class, [
                'label' => 'menu.department.title',
                'translation_domain' => 'messages',
            ])
            ->addFilter('model', ProductFilterType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'query_path' => 'equipmentRecord.product',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('type', ProductTypeFilterType::class, [
                'label' => 'crab.fields.type',
                'translation_domain' => 'crab',
                'query_path' => 'equipmentRecord.product.family.productType',
            ])
            ->addFilter('partNumber', TextFilterType::class, [
                'label' => 'spare_parts_request.fields.part_number',
                'translation_domain' => 'spare_parts_request',
                'query_path' => 'part.partNumber',
            ])
            ->addFilter('equipmentRecord', EquipmentRecordFilterType::class, [
                'label' => 'demo.fields.er_serial_number',
                'translation_domain' => 'demo',
                'query_path' => 'equipmentRecord',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('derogationStatus', TextFilterType::class, [
                'label' => 'crab.fields.derogation_status',
                'translation_domain' => 'crab',
                'query_path' => 'derogation.status',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'OPEN' => 'OPEN',
                        'ACCEPTED' => 'ACCEPTED',
                        'DENIED' => 'DENIED',
                    ],
                    'multiple' => true,
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
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'crab_show',
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,status,equipmentRecord.manufacturerLocation.name,createdAt,createdBy,fixedAt,fixedBy,fixingComments,inspectedAt,inspectedBy,inspectingComments,description,category,code.code,department.name,part.partNumber,equipmentRecord.serialNumber,equipmentRecord.model,questionParentId,questionSubject,questionDescription',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'crab.title.crabs',
            'translation_domain' => 'crab',
        ]);
    }
}
