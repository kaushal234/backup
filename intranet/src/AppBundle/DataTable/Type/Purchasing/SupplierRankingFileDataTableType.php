<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Purchasing;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\CurrencyColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Handler\ApiExistFilterHandler;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
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
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupplierRankingFileDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'purchasing/supplier_ranking/supplier_rankings';
    private const string EXPORT_COLUMNS = 'supplierNumber,supplierName,supplier.country,supplier.location,supplier.masterBuyer,revenue,supplier.currency,qualification,contracts,prices,minutesOfMeeting,codeEthic,iso9001,iso14001,esg,others,classification.name,isSupplierApproved,lastReviewAt,lastScreeningAt';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Client $client,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $classifications = iterator_to_array($this->client->findAll('purchasing/supplier_ranking/classifications', [], ['name']));
        $fileCategories = iterator_to_array($this->client->findAll('purchasing/supplier_ranking/file_categories', [], ['name']));

        $builder
            ->addColumn('supplierNumber', LinkColumnType::class, [
                'label' => 'fields.supplier_number',
                'sort' => 'supplier.code',
                'property_path' => '[supplier?][code]',
                'href' => function (string $supplierRankingCode, ApiData $supplierRanking): string {
                    return $this->urlGenerator->generate('supplier_rankings_show', ['id' => $supplierRanking['id']]);
                },
            ])

            ->addColumn('supplierName', LinkColumnType::class, [
                'label' => 'fields.supplier_name',
                'property_path' => '[supplier?][name]',
                'sort' => 'supplier.name',
                'href' => function (string $supplierRankingCode, ApiData $supplierRanking): string {
                    return $this->urlGenerator->generate('supplier_rankings_show', ['id' => $supplierRanking['id']]);
                },
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

        foreach ($fileCategories as $fileCategory) {
            $categoryId = $fileCategory['id'];
            $tooltipContent = \sprintf('<strong>%s</strong>', htmlspecialchars($fileCategory['name']));

            $builder->addColumn('file_category_'.$categoryId, TemplateColumnType::class, [
                'label' => $this->translator->trans(
                    \sprintf('headers.file_category_%d', $categoryId),
                    [],
                    'supplier_ranking'
                ),
                'template_path' => 'purchasing/supplier_ranking/file/partial/_column_file_category_status.html.twig',
                'template_vars' => static function (mixed $data, ColumnInterface $column) use ($categoryId): array {
                    return [
                        'category_id' => $categoryId,
                    ];
                },
                'property_path' => '[files]',
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
            ->addColumn('lastScreeningAt', DateTimeColumnType::class, [
                'label' => 'fields.last_screening_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('expired', TemplateColumnType::class, [
                'label' => 'fields.expired',
                'template_path' => 'purchasing/supplier_ranking/partial/_column_expired.html.twig',
                'getter' => static function ($data) {
                    return null;
                },
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'supplier_rankings_files_show',
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
            ->addFilter('hasExpiredFiles', BooleanFilterType::class, [
                'label' => 'fields.expired',
                'query_path' => 'hasExpiredFiles',
            ])
            ->addFilter('expertiseLevel', ExpertiseLevelFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('classification', ClassificationFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder->addExporter('xlsx', XlsxExporterType::class, [
            'extra_query_parameters' => [
                'pagination' => false,
                'columns' => self::EXPORT_COLUMNS,
            ],
        ]);

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));

        $builder->setDefaultFiltrationData(FiltrationData::fromArray([
            'classification' => array_values(array_map(
                static fn (ApiData $classification): string => $classification['@id'],
                array_filter(
                    $classifications,
                    static fn (ApiData $classification): bool => ($classification['name'] ?? null) !== 'Suppressed',
                )
            )),
            'supplierDisabled' => [0],
        ]));
    }

    public function buildView(DataTableView $view, DataTableInterface $dataTable, array $options): void
    {
        parent::buildView($view, $dataTable, $options);

        if (!$options['include_completion_review']) {
            return;
        }

        $totalCompletionRate = 0;
        $mandatoryCompletionRate = 0;

        try {
            $result = $this->client->get(self::RESOURCE.'/statistics', [
                'query' => $this->buildCompletionReviewQuery($dataTable),
            ]);

            $totalCompletionRate = $result['totalCompletionRate'] ?? 0;
            $mandatoryCompletionRate = $result['mandatoryCompletionRate'] ?? 0;
        } catch (ClientException) {
            // Rendered from a theme block (no controller/flash bag available here) - the
            // widget simply falls back to 0% on API failure.
        }

        $view->vars['completion_review'] = [
            'totalCompletionRate' => $totalCompletionRate,
            'mandatoryCompletionRate' => $mandatoryCompletionRate,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'submenu.files',
            'translation_domain' => 'supplier_ranking',
            'classifications' => [],
            'include_completion_review' => false,
        ]);
    }

    /**
     * Rebuilds the filter query sent to the statistics endpoint from the data table's own
     * filtration data (via DataTableInterface), rather than reaching into ApiProxyQuery's
     * internal state - keeps this in sync with whatever filters are actually active, using
     * only the bundle's public API.
     */
    private function buildCompletionReviewQuery(DataTableInterface $dataTable): array
    {
        $query = ['exists' => []];

        $filtrationData = $dataTable->getFiltrationData();

        if (null === $filtrationData) {
            return $query;
        }

        foreach ($dataTable->getFilters() as $name => $filter) {
            $filterData = $filtrationData->getFilterData($filter);

            if (null === $filterData || !$filterData->hasValue()) {
                continue;
            }

            if (DataTableBuilderInterface::SEARCH_FILTER_NAME === $name) {
                $query['q'] = $filterData->getValue();

                continue;
            }

            if ($filter->getConfig()->getHandler() instanceof ApiExistFilterHandler) {
                $query['exists'][$filter->getQueryPath()] = $filterData->getValue();
            } else {
                $query[$filter->getQueryPath()] = $filterData->getValue();
            }
        }

        return $query;
    }
}
