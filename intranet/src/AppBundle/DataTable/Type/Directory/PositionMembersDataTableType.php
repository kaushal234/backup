<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Column\Type\SimpleLinkColumnType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionMembersDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'people';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', PeopleColumnType::class, [
                'label' => 'fields.fullname',
                'header_translation_domain' => 'messages',
                'getter' => static fn (ApiData $row): array => $row->toArray(),
                'sort' => 'lastname',
            ])
            ->addColumn('email', TextColumnType::class, [
                'label' => 'directory.people.fields.email',
                'header_translation_domain' => 'directory',
            ])
            ->addColumn('businessUnit', SimpleLinkColumnType::class, [
                'label' => 'directory.people.fields.businessUnit',
                'header_translation_domain' => 'directory',
                'property_path' => '[businessUnit?][name]',
                'route' => 'directory_business_units_show',
                'key' => 'id',
                'property_path_link' => '[businessUnit][id]',
                'sort' => 'businessUnit.name',
            ])
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search): void {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'name' => 'asc',
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
        $resolver->setRequired('title');
        $resolver->setAllowedTypes('title', 'string');
    }
}
