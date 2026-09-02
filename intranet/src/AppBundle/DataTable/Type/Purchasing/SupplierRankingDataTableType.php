<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Purchasing;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\CurrencyColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\BuyerFilterType;
use AppBundle\DataTable\Filter\Type\Purchasing\ClassificationFilterType;
use AppBundle\DataTable\Filter\Type\Purchasing\ExpertiseLevelFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupplierRankingDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'purchasing/supplier_ranking/supplier_rankings';
    private const string EXPORT_COLUMNS = 'supplierNumber,supplierName,supplier.country,supplier.location,supplier.masterBuyer,expertiseLevel.name,revenue,supplier.currency,cost,logistic,communicationTransparencyResponsiveness,productFieldSupport,environmentalSocialGovernance,antiCorruption,cybersecurity,classification.name,isSupplierApproved,lastReviewAt,lastReviewBy,nextReviewAt,lastScreeningAt,lastScreeningBy';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $classificationsMap = [];
        foreach ($options['classifications'] as $classification) {
            $classificationsMap[$classification['@id']] = $classification['name'];
        }

        $builder
            ->addColumn('supplierNumber', LinkColumnType::class, [
                'label' => 'fields.supplier_number',
                'property_path' => '[supplier?][code]',
                'href' => function (string $supplierRankingCode, ApiData $supplierRanking): string {
                    return $this->urlGenerator->generate('supplier_rankings_show', ['id' => $supplierRanking['id']]);
                },
                'sort' => 'supplier.code',
            ])
            ->addColumn('supplierName', LinkColumnType::class, [
                'label' => 'fields.supplier_name',
                'property_path' => '[supplier?][name]',
                'href' => function (string $supplierRankingCode, ApiData $supplierRanking): string {
                    return $this->urlGenerator->generate('supplier_rankings_show', ['id' => $supplierRanking['id']]);
                },
                'sort' => 'supplier.name',
            ])
            ->addColumn('location', LocationColumnType::class, [
                'label' => 'fields.location',
                'property_path' => '[supplier?][location?]',
                'sort' => 'supplier.location.name',
            ])
            ->addColumn('buyer', PeopleColumnType::class, [
                'label' => 'fields.buyer',
                'header_translation_domain' => 'supplier_ranking',
                'property_path' => '[supplier?][masterBuyer]',
                'sort' => 'supplier.masterBuyer.lastname',
            ])
            ->addColumn('expertiseLevel', TextColumnType::class, [
                'label' => 'fields.expertise_level',
                'property_path' => '[expertiseLevel?][name?]',
                'sort' => 'expertiseLevel.name',
            ])
            ->addColumn('revenue', TextColumnType::class, [
                'label' => 'fields.revenue',
                'sort' => true,
                'formatter' => static function (mixed $value): string {
                    if (null === $value) {
                        return '';
                    }

                    return number_format((float) $value, 0, '.', ',');
                },
            ])
            ->addColumn('currency', CurrencyColumnType::class, [
                'property_path' => '[supplier?][currency?]',
                'sort' => 'supplier.currency.name',
            ]);

        foreach ($options['criterias'] as $criteria) {
            $criteriaId = $criteria['id'];
            $tooltipContent = \sprintf('<strong>%s</strong><hr>', htmlspecialchars($criteria['name']));
            $tooltipContent .= implode('', array_map(
                fn (int $note) => \sprintf(
                    '<p>%d: %s</p>',
                    $note,
                    $this->translator->trans(
                        \sprintf('headers.criteria_%d_%d', $criteriaId, $note),
                        [],
                        'supplier_ranking'
                    )
                ),
                [5, 4, 3, 2, 1]
            ));

            $builder->addColumn('criteria_'.$criteriaId, TemplateColumnType::class, [
                'label' => $this->translator->trans(
                    \sprintf('headers.criteria_%d', $criteriaId),
                    [],
                    'supplier_ranking'
                ),
                'sort' => 'criteria_'.$criteriaId,
                'getter' => static function ($data) {
                    return null;
                },
                'template_path' => 'purchasing/supplier_ranking/partial/_column_notation.html.twig',
                'template_vars' => static function (mixed $data, ColumnInterface $column) use ($criteriaId): array {
                    return [
                        'criteria_id' => $criteriaId,
                    ];
                },
                'value_attr' => ['class' => 'text-center'],
                'header_attr' => [
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-html' => 'true',
                    'data-placement' => 'top',
                    'title' => $tooltipContent,
                ],
            ]);
        }

        $builder
            ->addColumn('classification', TemplateColumnType::class, [
                'label' => 'fields.classification',
                'template_path' => 'purchasing/supplier_ranking/partial/_column_classification.html.twig',
                'sort' => 'classification.name',
            ])
            ->addColumn('supplierApproved', TemplateColumnType::class, [
                'label' => 'fields.supplier_approved',
                'template_path' => 'purchasing/supplier_ranking/partial/_column_supplier_approved.html.twig',
                'sort' => 'classification.isSupplierApproved',
            ])
            ->addColumn('lastReviewAt', DateTimeColumnType::class, [
                'label' => 'fields.last_review_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('nextReviewAt', DateTimeColumnType::class, [
                'label' => 'fields.next_review_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('lastScreeningAt', DateTimeColumnType::class, [
                'label' => 'fields.last_screening_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'supplier_rankings_show',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('supplierNumber', TextFilterType::class, [
                'query_path' => 'supplier.code',
                'label' => 'fields.supplier_number',
            ])
            ->addFilter('supplierName', TextFilterType::class, [
                'label' => 'fields.supplier_name',
                'query_path' => 'supplier.name',
            ])
            ->addFilter('buyer', BuyerFilterType::class, [
                'label' => 'fields.buyer',
            ])
            ->addFilter('isSupplierApproved', TextFilterType::class, [
                'label' => 'fields.supplier_approved',
                'query_path' => 'classification.isSupplierApproved',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'Unapproved' => 0,
                        'Approved' => 1,
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('supplierDisabled', TextFilterType::class, [
                'label' => 'fields.supplier_disabled',
                'form_type' => SelectFormType::class,
                'query_path' => 'exists[disabledAt]',
                'form_options' => [
                    'choices' => [
                        'no' => 0,
                        'yes' => 1,
                    ],
                    'multiple' => true,
                ],
                'active_filter_formatter' => static function (FilterData $data): string {
                    $map = [0 => 'no', 1 => 'yes'];
                    $values = (array) $data->getValue();

                    return implode(', ', array_map(
                        static fn (mixed $value) => $map[$value] ?? (string) $value,
                        $values
                    ));
                },
            ])
            ->addFilter('location', LocationFilterType::class, [
                'label' => 'fields.location',
            ])
            ->addFilter('expertiseLevel', ExpertiseLevelFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('classification', ClassificationFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('criteriaEnvironmentalSocialGovernance', TextFilterType::class, [
                'label' => 'headers.criteria_7',
                'query_path' => 'criteria_7',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'N/A' => 'null',
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5,
                    ],
                    'placeholder' => '',
                    'multiple' => true,
                ],
                'active_filter_formatter' => static function (FilterData $data): string {
                    $value = $data->getValue();

                    if (!\is_array($value)) {
                        return 'null' === (string) $value ? 'N/A' : (string) $value;
                    }

                    return implode(', ', array_map(
                        static fn ($item) => 'null' === (string) $item ? 'N/A' : (string) $item,
                        $value,
                    ));
                },
            ])
            ->addFilter('criteriaAntiCorruption', TextFilterType::class, [
                'label' => 'headers.criteria_8_full',
                'query_path' => 'criteria_8',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'N/A' => 'null',
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5,
                    ],
                    'placeholder' => '',
                ],
                'active_filter_formatter' => static function (FilterData $data): string {
                    return 'null' === (string) $data->getValue() ? 'N/A' : (string) $data->getValue();
                },
            ])
            ->addFilter('criteriaCybersecurity', TextFilterType::class, [
                'label' => 'headers.criteria_9_full',
                'query_path' => 'criteria_9',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'N/A' => 'null',
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5,
                    ],
                    'placeholder' => '',
                ],
                'active_filter_formatter' => static function (FilterData $data): string {
                    return 'null' === (string) $data->getValue() ? 'N/A' : (string) $data->getValue();
                },
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder->addExporter('xlsx', XlsxExporterType::class, [
            'extra_query_parameters' => [
                'itemsPerPage' => 40000,
                'columns' => self::EXPORT_COLUMNS,
            ],
        ]);

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));

        $builder->setDefaultFiltrationData(FiltrationData::fromArray([
            'classification' => array_values(array_filter(
                array_keys($classificationsMap),
                static fn (string $iri) => !str_ends_with($iri, '/5'),
            )),
            'supplierDisabled' => [0],
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'title',
            'translation_domain' => 'supplier_ranking',
            'criterias' => [],
            'classifications' => [],
        ]);
    }
}
