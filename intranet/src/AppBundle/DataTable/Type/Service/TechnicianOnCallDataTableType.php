<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Service;

use AppBundle\DataTable\Column\Type\AirportColumnType;
use AppBundle\DataTable\Column\Type\CustomerColumnType;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Column\Type\TocCommentsColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationSSOFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductFilterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductTypeFilterType;
use AppBundle\DataTable\Filter\Type\Service\ServiceActivityFilterType;
use AppBundle\DataTable\Filter\Type\Service\ServiceAreaFilterType;
use AppBundle\DataTable\Filter\Type\Service\TechnicianOnCallTagFilterType;
use AppBundle\DataTable\Filter\Type\Service\TechnicianOnCallTypeFilterType;
use AppBundle\DataTable\Filter\Type\Support\UnitOperationalStatusFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\HtmlColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'service/technician_on_calls';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'display.table.scar_files.headers.id',
                'header_translation_domain' => 'messages',
                'route' => 'technician_on_calls_show',
                'sort' => true,
            ])
            ->addColumn('salesOrganisationService', LocationColumnType::class, [
                'label' => 'fields.sso',
                'property_path' => '[salesOrganisationService]',
                'header_translation_domain' => 'messages',
                'sort' => 'salesOrganisationService.name',
            ])
            ->addColumn('assignee', PeopleColumnType::class, [
                'label' => 'task.fields.assignee',
                'header_translation_domain' => 'task',
                'sort' => 'assignee.lastname',
            ])
            ->addColumn('technician', PeopleColumnType::class, [
                'label' => 'toc.fields.technician',
                'header_translation_domain' => 'technician_on_call',
                'sort' => 'technician.lastname',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('serviceActivity', TextColumnType::class, [
                'label' => 'toc.fields.service_activity',
                'header_translation_domain' => 'technician_on_call',
                'property_path' => '[serviceActivity][name]',
                'sort' => 'serviceActivity.name',
            ])
            ->addColumn('technicianOnCallType', TextColumnType::class, [
                'label' => 'toc.fields.technician_on_call_type',
                'header_translation_domain' => 'technician_on_call',
                'value_translation_domain' => 'technician_on_call',
                'property_path' => '[technicianOnCallType][name]',
                'sort' => 'technicianOnCallType.name',
            ])
            ->addColumn('indiceFactor', LabelColumnType::class, [
                'label' => 'fields.ifactor',
                'header_translation_domain' => 'messages',
                'label_classes' => [
                    'IF 1' => 'default',
                    'IF 10' => 'primary',
                    'IF 100' => 'warning',
                    'IF 1000' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('unitOperationalStatus', TextColumnType::class, [
                'label' => 'toc.fields.unit_operational_status_short',
                'header_translation_domain' => 'technician_on_call',
                'property_path' => '[unitOperationalStatus?][name]',
                'sort' => 'unitOperationalStatus.name',
            ])
            ->addColumn('equipmentRecord', HtmlColumnType::class, [
                'label' => 'toc.fields.model',
                'header_translation_domain' => 'technician_on_call',
                'formatter' => static function (array $value) {
                    return \sprintf('%s <br/> %s', $value['model'], $value['serialNumber']);
                },
                'sort' => 'equipmentRecord.serialNumber',
            ])
            ->addColumn('mainContact', PeopleColumnType::class, [
                'label' => 'toc.fields.main_contact.label',
                'header_translation_domain' => 'technician_on_call',
                'sort' => 'assignee.lastname',
            ])
            ->addColumn('title', TextColumnType::class, [
                'label' => 'toc.fields.title',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
            ->addColumn('comments', TocCommentsColumnType::class, [
                'label' => 'toc.fields.recent_comments',
                'header_translation_domain' => 'technician_on_call',
            ])
            ->addColumn('errorCodes', TextColumnType::class, [
                'label' => 'toc.fields.error_codes.label',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
                'visible' => false,
            ])
            ->addColumn('updatedAt', DateColumnType::class, [
                'label' => 'toc.fields.last_update',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
            ->addColumn('factoryFlag', BooleanColumnType::class, [
                'label' => 'toc.fields.factoryFlag',
                'sort' => true,
            ])
            ->addColumn('confidential', BooleanColumnType::class, [
                'label' => 'toc.fields.confidential.label',
            ])
            ->addColumn('createdBy', PeopleColumnType::class, [
                'label' => 'fields.created_by',
                'header_translation_domain' => 'messages',
                'sort' => 'createdBy.lastname',
                'visible' => false,
            ])
            ->addColumn('airport', AirportColumnType::class, [
                'sort' => 'airport.code',
                'visible' => false,
            ])
            ->addColumn('customer', CustomerColumnType::class, [
                'label' => 'toc.fields.customer',
                'header_translation_domain' => 'technician_on_call',
                'sort' => 'customer.name',
                'visible' => false,
            ])
            ->addColumn('openDays', TextColumnType::class, [
                'label' => 'toc.fields.open_days',
                'header_translation_domain' => 'technician_on_call',
                'sort' => false,
                'visible' => false,
            ])
            ->addColumn('daysWithoutActivity', TextColumnType::class, [
                'label' => 'toc.fields.days_without_activity',
                'header_translation_domain' => 'technician_on_call',
                'sort' => false,
                'visible' => false,
            ])
            ->addColumn('serialNumber', TextColumnType::class, [
                'label' => 'toc.fields.serialNumber',
                'header_translation_domain' => 'technician_on_call',
                'sort' => false,
                'visible' => false,
            ])
            ->addColumn('thirdPartyRef', TextColumnType::class, [
                'label' => 'toc.fields.third_party_ref.label',
                'header_translation_domain' => 'technician_on_call',
                'sort' => false,
                'visible' => false,
            ])
        ;

        $builder
            ->addFilter('status', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'label' => 'toc.fields.status',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'choices' => [
                        'PENDING' => 'PENDING',
                        'IN_PROGRESS' => 'IN_PROGRESS',
                        'SUSPENDED' => 'SUSPENDED',
                        'SOLVED' => 'SOLVED',
                        'CLOSED' => 'CLOSED',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('assignee', PeopleFilterType::class, [
                'label' => 'tasks.assignee',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('serialNumberEquipmentRecord', TextFilterType::class, [
                'label' => 'demo.fields.er_serial_number',
                'translation_domain' => 'demo',
                'query_path' => 'equipmentRecord.serialNumber',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('indiceFactor', TextFilterType::class, [
                'label' => 'fields.ifactor',
                'translation_domain' => 'messages',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'IF 1' => 'IF 1',
                        'IF 10' => 'IF 10',
                        'IF 100' => 'IF 100',
                        'IF 1000' => 'IF 1000',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('actor', PeopleFilterType::class, [
                'label' => 'toc.filters.actor',
                'translation_domain' => 'technician_on_call',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('airport', AirportFilterType::class, [
                'label' => 'toc.filters.by_airport',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('solvedAt', DateRangeFilterType::class, [
                'label' => 'toc.fields.solved_at',
            ])
            ->addFilter('unitOperationalStatus', UnitOperationalStatusFilterType::class, [
                'label' => 'toc.fields.unit_operational_status_short',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('technician', PeopleFilterType::class, [
                'label' => 'toc.fields.technician',
                'translation_domain' => 'technician_on_call',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('factoryFlag', BooleanFilterType::class)
            ->addFilter('confidential', BooleanFilterType::class)
            ->addFilter('late', BooleanFilterType::class, [
                'label' => 'toc.filters.late',
                'translation_domain' => 'technician_on_call',
            ])
            ->addFilter('salesOrganisation', LocationSSOFilterType::class, [
                'label' => 'toc.filters.sso',
                'translation_domain' => 'technician_on_call',
                'query_path' => 'equipmentRecord.salesOrganisation',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('factory', LocationFilterType::class, [
                'label' => 'toc.filters.location',
                'translation_domain' => 'technician_on_call',
                'query_path' => 'equipmentRecord.manufacturerLocation',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('factoryFlagRecentlyClosed', BooleanFilterType::class)
            ->addFilter('surveyAnswerThisMonth', BooleanFilterType::class, [
                'label' => 'toc.filters.survey',
            ])
            ->addFilter('salesOrganisationService', LocationSSOFilterType::class, [
                'label' => 'toc.filters.ssoService',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('buyer', CustomerFilterType::class, [
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
                'query_path' => 'equipmentRecord.buyer',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('country', CountryFilterType::class, [
                'label' => 'settings.address.form.country.label',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
                'query_path' => 'airport.country',
            ])
            ->addFilter('technicianOnCallParts', TextFilterType::class, [
                'label' => 'toc.filters.toc_parts',
                'translation_domain' => 'technician_on_call',
                'query_path' => 'parts.partNumber',
            ])
            ->addFilter('tags', TechnicianOnCallTagFilterType::class, [
                'label' => 'toc.fields.tags.label',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'uri' => 'technician_on_call_tags',
                    'translation_domain' => 'technician_on_call',
                    'multiple' => true,
                ],
            ])
            ->addFilter('endUser', CustomerFilterType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
                'query_path' => 'equipmentRecord.endUser',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('product', ProductFilterType::class, [
                'label' => 'toc.card.model',
                'translation_domain' => 'technician_on_call',
                'query_path' => 'equipmentRecord.product',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('sparePartsRequestParts', TextFilterType::class, [
                'label' => 'toc.filters.spr_parts',
                'translation_domain' => 'technician_on_call',
                'query_path' => 'sparePartsRequests.parts.partNumber',
            ])
            ->addFilter('technicianOnCallType', TechnicianOnCallTypeFilterType::class, [
                'label' => 'toc.fields.technician_on_call_type',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('maintainer', CustomerFilterType::class, [
                'label' => 'fields.maintainer',
                'translation_domain' => 'messages',
                'query_path' => 'equipmentRecord.maintainer',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('productType', ProductTypeFilterType::class, [
                'label' => 'catalogue.family.type',
                'translation_domain' => 'catalogue',
                'query_path' => 'equipmentRecord.product.family.productType',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('title', TextFilterType::class, [
                'label' => 'toc.fields.title',
                'translation_domain' => 'technician_on_call',
            ])
            ->addFilter('serviceActivity', ServiceActivityFilterType::class, [
                'label' => 'toc.fields.service_activity',
                'translation_domain' => 'technician_on_call',
                'form_options' => [
                    'multiple' => true,
                ],
            ])
            ->addFilter('createdBy', PeopleFilterType::class, [
                'label' => 'fields.created_by',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('errorCodes', TextFilterType::class, [
                'label' => 'toc.fields.error_codes.label',
                'translation_domain' => 'technician_on_call',
            ])
            ->addFilter('serviceAreas', ServiceAreaFilterType::class, [
                'label' => 'service_area.fields.service_areas_column',
                'translation_domain' => 'service',
                'form_options' => ['multiple' => true],
                'query_path' => 'airport.serviceAreas',
            ])
            ->addFilter('thirdPartyRef', TextFilterType::class, [
                'label' => 'toc.fields.third_party_ref.label',
                'translation_domain' => 'technician_on_call',
            ])
        ;

        $builder
            ->setDefaultFiltrationData(new FiltrationData([
                'status' => new FilterData(value: ['PENDING', 'IN_PROGRESS']),
            ]))
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
            ->setDefaultExportData(DefaultExportData::fromDefaultArray())
            ->addExporter('csv', CsvExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,createdAt,createdBy,salesOrganisationService.name,assignee,technician,status,serviceActivity.name,technicianOnCallType.name,indiceFactor,unitOperationalStatus.name,equipmentRecord.model,equipmentRecord.serialNumber,mainContact.fullName,title,updatedAt,factoryFlag,airport.code,customer.name,openDays,daysWithoutActivity,csrList,sparePartsRequest.count,equipmentRecord.manufacturerLocation.name,solvedAt,daysToSolved,factoryFlagHistory,hourmeter,partsList,sprList,warrantyLegacyId,thirdPartyName,thirdPartyRef',
                ],
            ])
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,createdAt,createdBy,salesOrganisationService.name,assignee,technician,status,serviceActivity.name,technicianOnCallType.name,indiceFactor,unitOperationalStatus.name,equipmentRecord.model,equipmentRecord.serialNumber,mainContact.fullName,title,updatedAt,factoryFlag,airport.code,customer.name,openDays,daysWithoutActivity,csrList,sparePartsRequest.count,equipmentRecord.manufacturerLocation.name,solvedAt,daysToSolved,factoryFlagHistory,hourmeter,partsList,sprList,warrantyLegacyId,thirdPartyName,thirdPartyRef',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'toc.title.main',
            'translation_domain' => 'technician_on_call',
        ]);
    }
}
