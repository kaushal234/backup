<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\News;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\FilesTooltipColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DepartmentFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DivisionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Premise\PremiseFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsListDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'news';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('title', TextColumnType::class, [
                'label' => 'news.fields.title',
                'header_translation_domain' => 'news',
            ])
            ->addColumn('category', TextColumnType::class, [
                'property_path' => '[category?][name]',
                'label' => 'news.fields.category',
                'header_translation_domain' => 'news',
            ])
            ->addColumn('date', DateTimeColumnType::class, [
                'label' => 'cleanliness.fields.date',
                'header_translation_domain' => 'cleanliness',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('people', PeopleTooltipColumnType::class, [
                'label' => 'fields.poster',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('contentShort', TextColumnType::class, [
                'label' => 'news.fields.short_content',
                'header_translation_domain' => 'news',
            ])
            ->addColumn('files', FilesTooltipColumnType::class, [
                'label' => 'news.fields.images',
                'header_translation_domain' => 'news',
            ])
            ->addFilter('division', DivisionFilterType::class, [
                'label' => 'menu.division.title',
                'translation_domain' => 'messages',
            ])
            ->addFilter('department', DepartmentFilterType::class, [
                'label' => 'directory.department.name',
                'translation_domain' => 'directory',
            ])
            ->addFilter('premise', PremiseFilterType::class, [
                'label' => 'directory.premise.title',
                'translation_domain' => 'directory',
            ])
            // Simple search top right, using 'q' parameter of ApiPlatform
           ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
               $query->search($search);
           })
        ;
        $builder->setDefaultSortingData(SortingData::fromArray([
            'date' => 'desc',
        ]));
        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'news_show',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'evendors_news.title',
            'translation_domain' => 'news',
        ]);
    }
}
