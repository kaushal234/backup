<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeatureDataTableType extends AbstractDataTableType
{
    public const string MEMBERS = 'people?acls.group.features=%s&disabled=0&hidden=0&normalization_groups[0]=group_member';

    public const string RESOURCE = 'features';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'display.table.scar_files.headers.id',
                'header_translation_domain' => 'messages',
                'route' => 'mis_features_show',
                'sort' => true,
            ])
            ->addColumn('name', TextColumnType::class, [
                'label' => 'support_team.fields.name',
                'header_translation_domain' => 'support_team',
                'sort' => true,
            ])
        ;
        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'asc',
            ]))
            ->setDefaultPaginationData(new PaginationData(
                page: 1,
                perPage: 10,
            ))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'mis_features_show',
            ])
        ;
        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'mis.member.title',
            'translation_domain' => 'mis',
        ]);
    }
}
