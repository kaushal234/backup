<?php

declare(strict_types=1);

namespace App\DataTable\Type\WarrantyClaim;

use App\DataTable\Action\Type\ShowButtonActionType;
use App\DataTable\Filter\Type\ChoiceFilterType;
use App\DataTable\Filter\Type\TextFilterType;
use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\HtmlColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AutoconfigureTag(name: 'kreyu_data_table.type')]
class WarrantyClaimDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', TextColumnType::class, [
                'label' => 'WC#',
                'sort' => true,
            ])
            ->addColumn('serialNumber', LinkColumnType::class, [
                'label' => 'calibration_tools.tools.fields.serialNumber',
                'sort' => true,
                'href' => fn (?string $serialNumber): string => null === $serialNumber
                    ? '#'
                    : $this->urlGenerator->generate('equipment:show_by_serial_number', [
                        'serialNumber' => $serialNumber,
                    ]),
                'value_attr' => [
                    'class' => 'text-primary text-decoration-underline',
                ],
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'sort' => true,
            ])
            ->addColumn('description', HtmlColumnType::class, [
                'label' => 'fields.description',
            ])
            ->addColumn('claimDate', DateTimeColumnType::class, [
                'label' => 'display.table.vwc_wc.header.claimDate',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('type', TextColumnType::class, [
                'label' => 'display.table.vwc.headers.type',
                'sort' => true,
            ])
            ->addColumn('equipmentModel', TextColumnType::class, [
                'label' => 'display.table.vwc_wc.header.model',
                'sort' => true,
            ])
            ->addColumn('equipmentLocation', HtmlColumnType::class, [
                'label' => 'display.table.vwc_wc.header.equipmentLocation',
                'sort' => true,
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search): void {
            $query->search($search);
        });

        $builder->addFilter('status', ChoiceFilterType::class, [
            'form_options' => [
                'placeholder' => '',
                'choices' => [
                    'Pending' => 'PENDING',
                    'Accepted' => 'ACCEPTED',
                    'Conditional' => 'CONDITIONAL',
                    'Rejected' => 'REJECTED',
                    'Sales Concession' => 'SALES CONCESSION',
                ],
            ],
        ]);
        $builder->addFilter('serialNumber', TextFilterType::class);

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'warranty_claim:show',
        ]);

        $builder->setDefaultSortingData(SortingData::fromArray(['id' => 'desc']));
        $builder->setDefaultPaginationData(new PaginationData(page: 1, perPage: 10));
    }

    public function buildView(DataTableView $view, DataTableInterface $dataTable, array $options): void
    {
        parent::buildView($view, $dataTable, $options);
        $view->vars['count_label'] = $options['count_label'];
        $view->vars['search_placeholder'] = $options['search_placeholder'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'extranet.title.warranty_claims',
            'translation_domain' => 'messages',
            'count_label' => 'WC',
            'search_placeholder' => 'extranet.warranty_claim.search_placeholder',
        ]);
    }
}
