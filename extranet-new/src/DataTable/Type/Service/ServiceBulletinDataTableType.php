<?php

declare(strict_types=1);

namespace App\DataTable\Type\Service;

use App\DataTable\Action\Type\ShowButtonActionType;
use App\DataTable\Filter\Type\ChoiceFilterType;
use App\DataTable\Filter\Type\DateRangeFilterType;
use App\DataTable\Filter\Type\TextFilterType;
use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AutoconfigureTag(name: 'kreyu_data_table.type')]
class ServiceBulletinDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', TextColumnType::class, [
                'label' => '#',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('ssdDecidedAt', DateTimeColumnType::class, [
                'label' => 'extranet.fields.created_date',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('category', TextColumnType::class, [
                'label' => 'fields.category',
                'sort' => true,
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'sort' => true,
            ])
            ->addColumn('type', TextColumnType::class, [
                'label' => 'Type',
                'sort' => true,
            ])
            ->addColumn('title', TextColumnType::class, [
                'label' => 'Title',
                'sort' => true,
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'Description',
                'visible' => false,
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->setDefaultSortingData(SortingData::fromArray(['ssdDecidedAt' => 'desc']));
        $builder->setDefaultPaginationData(new PaginationData(page: 1, perPage: 10));

        $builder->addFilter('title', TextFilterType::class);
        $builder->addFilter('type', ChoiceFilterType::class, [
            'form_options' => [
                'placeholder' => '',
                'choices' => [
                    'Improvement' => 'IMPROVEMENT',
                    'Maintenance' => 'MAINTENANCE',
                    'Operation' => 'OPERATION',
                ],
            ],
        ]);
        $builder->addFilter('ssdDecidedAt', DateRangeFilterType::class, [
            'label' => 'fields.created_at',
            'translation_domain' => 'messages',
        ]);

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'service_bulletin:show',
        ]);
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
            'title' => 'Service Bulletins',
            'count_label' => 'SB',
            'search_placeholder' => 'extranet.service_bulletin.search_placeholder',
        ]);
    }
}
