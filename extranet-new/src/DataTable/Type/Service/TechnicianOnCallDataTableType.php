<?php

declare(strict_types=1);

namespace App\DataTable\Type\Service;

use App\DataTable\Action\Type\ShowButtonActionType;
use App\DataTable\Column\EquipmentRecordColumnType;
use App\DataTable\Column\ServiceActivityColumnType;
use App\DataTable\Column\UnitOperationalStatusColumnType;
use App\DataTable\Column\UserColumnType;
use App\DataTable\Exporter\Type\CsvExporterType;
use App\DataTable\Exporter\Type\XlsxExporterType;
use App\DataTable\Filter\Type\AirportFilterType;
use App\DataTable\Filter\Type\ServiceActivityFilterType;
use App\DataTable\Filter\Type\TextFilterType;
use App\DataTable\Filter\Type\UnitOperationalStatusFilterType;
use App\DataTable\Query\ApiProxyQuery;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\Customer;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportData;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportStrategy;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag(name: 'kreyu_data_table.type')]
class TechnicianOnCallDataTableType extends AbstractDataTableType
{
    private const string EXPORT_COLUMNS = 'id,createdAt,solvedAt,status,serviceActivity.name,equipmentRecord.buyer.name,equipmentRecord.endUser.name,mainContact.fullName,airport.code,unitOperationalStatus.name,equipmentRecord.model,equipmentRecord.serialNumber,equipmentRecord.customerSerialNumber,title,salesOrganisationService.name,assignee';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', TextColumnType::class, [
                'label' => 'toc.card.toc',
                'sort' => true,
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'extranet.fields.created_date',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('mainContact', UserColumnType::class, [
                'label' => 'toc.fields.main_contact.label',
                'header_translation_domain' => 'technician_on_call',
                'sort' => 'assignee.lastname',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'header_translation_domain' => 'messages',
                'sort' => true,
                'header_attr' => [
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => $this->translator->trans('toc.messages.help.status_definitions', [], 'technician_on_call'),
                ],
            ])
            ->addColumn('serviceActivity', ServiceActivityColumnType::class, [
                'sort' => 'serviceActivity.name',
            ])
            ->addColumn('airport', TextColumnType::class, [
                'label' => 'toc.fields.airport',
                'sort' => 'airport.code',
                'formatter' => static function (Airport $airport): string {
                    return $airport->code;
                },
            ])
            ->addColumn('unitOperationalStatus', UnitOperationalStatusColumnType::class, [
                'sort' => 'unitOperationalStatus.name',
            ])
            ->addColumn('equipmentRecord', EquipmentRecordColumnType::class, [
                'sort' => 'equipmentRecord.serialNumber',
            ])
            ->addColumn('customer', TextColumnType::class, [
                'label' => 'fields.customer',
                'header_translation_domain' => 'messages',
                'sort' => 'customer.name',
                'formatter' => static function (Customer $customer): string {
                    return $customer->name;
                },
            ])
            ->addColumn('title', TextColumnType::class, [
                'label' => 'toc.fields.title',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
        ;
        $builder->addFilter('status', TextFilterType::class, [
            'label' => 'toc.fields.status',
            'form_type' => ChoiceType::class,
            'form_options' => [
                'choices' => [
                    'PENDING' => 'PENDING',
                    'IN_PROGRESS' => 'IN_PROGRESS',
                    'SUSPENDED' => 'SUSPENDED',
                    'SOLVED' => 'SOLVED',
                    'CLOSED' => 'CLOSED',
                ],
                'multiple' => true,
                'autocomplete' => true,
            ],
        ]);

        $builder->addFilter('serviceActivity', ServiceActivityFilterType::class, [
            'label' => 'toc.fields.service_activity',
            'form_options' => [
                'multiple' => true,
                'autocomplete' => true,
            ],
        ]);

        $builder->addFilter('airport', AirportFilterType::class, [
            'label' => 'toc.fields.airport',
            'value_extractor' => static fn (Airport $airport) => $airport->getIri(),
            'form_options' => [
                'multiple' => true,
            ],
        ]);

        $builder->addFilter('unitOperationalStatus', UnitOperationalStatusFilterType::class, [
            'label' => 'toc.fields.unit_operational_status_short',
            'form_options' => [
                'multiple' => true,
                'autocomplete' => true,
            ],
        ]);

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'technician_on_call:show',
        ]);

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));

        $builder->setDefaultFiltrationData(FiltrationData::fromArray([
            'status' => ['PENDING', 'IN_PROGRESS', 'SUSPENDED'],
        ]));

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addExporter('csv', CsvExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => self::EXPORT_COLUMNS,
                ],
            ])
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => self::EXPORT_COLUMNS,
                ],
            ])
            ->setDefaultExportData(ExportData::fromArray([
                'filename' => \sprintf('technician_on_call_%s', date('Y-m-d')),
                'exporter' => 'xlsx',
                'strategy' => ExportStrategy::IncludeCurrentPage,
                'include_personalization' => true,
            ]))
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
