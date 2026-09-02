<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PremiseListDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'premises';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'directory.department.fields.name',
                'sort' => true,
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'directory.position.fields.description',
                'sort' => true,
            ])
            ->addColumn('tags', CollectionColumnType::class, [
                'label' => 'directory.region.fields.type',
                'entry_type' => TextColumnType::class,
                'property_path' => 'tags',
                'entry_options' => [
                    'formatter' => static function ($tag): string {
                        return $tag['name'];
                    },
                ],
            ])
            ->addFilter('name', TextFilterType::class, [
            ])
            ->addFilter('description', TextFilterType::class, [
            ])
            ->addFilter('archived', BooleanFilterType::class, [
                'label' => 'Archived',
            ])
           ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
               $query->search($search);
           })
            ->setDefaultSortingData(SortingData::fromArray([
                'name' => 'asc',
            ]))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'directory_premise_show',
            ]);

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,name,description,latitude,longitude,supportTeam',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'directory.premise.list.list',
            'translation_domain' => 'directory',
        ]);
    }
}
