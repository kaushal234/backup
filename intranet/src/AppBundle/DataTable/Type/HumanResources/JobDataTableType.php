<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\HumanResources;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\BusinessUnitColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'jobs';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('title', TextColumnType::class, [
                'label' => 'jobs.fields.title',
                'header_translation_domain' => 'job',
            ])
            ->addColumn('businessUnit', BusinessUnitColumnType::class, [
                'label' => 'directory.business_unit.name',
                'header_translation_domain' => 'directory',
                'property_path' => '[businessUnit?][name]',
                'sort' => 'businessUnit.name',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('createdBy', PeopleTooltipColumnType::class, [
                'label' => 'fields.poster',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('enabled', BooleanColumnType::class, [
                'label' => 'jobs.title.open',
                'header_translation_domain' => 'job',
            ])
            ->addColumn('synchronized', BooleanColumnType::class, [
                'label' => 'jobs.fields.synchronized',
                'header_translation_domain' => 'job',
            ]);
        $builder
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('businessUnit', BusinessUnitFilterType::class, [
                'label' => 'directory.business_unit.name',
                'translation_domain' => 'directory',
            ])
            ->addFilter('enabled', BooleanFilterType::class);
        $builder
            // Simple search top right, using 'q' parameter of ApiPlatform
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultFiltrationData(new FiltrationData([
                'enabled' => new FilterData(value: true),
            ]))
            ->setDefaultSortingData(SortingData::fromArray([
                'createdAt' => 'desc',
            ]))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'human_resources_job_show',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'jobs.title.list',
            'translation_domain' => 'job',
        ]);
    }
}
