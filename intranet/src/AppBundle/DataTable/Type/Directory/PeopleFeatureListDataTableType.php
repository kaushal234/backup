<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleFeatureListDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'people';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('lastname', TextColumnType::class, [
                'label' => 'contacts.fields.lastname',
                'header_translation_domain' => 'contacts',
                'sort' => true,
            ])
            ->addColumn('firstname', TextColumnType::class, [
                'label' => 'contacts.fields.firstname',
                'header_translation_domain' => 'contacts',
                'sort' => true,
            ])
            ->addColumn('email', TextColumnType::class, [
                'label' => 'contacts.fields.email',
                'header_translation_domain' => 'contacts',
            ])
            ->addColumn('jobTitle', TextColumnType::class, [
                'label' => 'display.table.representative.headers.title',
                'header_translation_domain' => 'messages',
            ])
            ->setDefaultFiltrationData(new FiltrationData([
                'hidden' => new FilterData(value: false),
            ]))
            ->setDefaultFiltrationData(new FiltrationData([
                'disabled' => new FilterData(value: false),
            ]))
            // Simple search, using 'q' parameter of ApiPlatform
           ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
               $query->search($search);
           })
            ->setDefaultSortingData(SortingData::fromArray([
                'lastname' => 'asc',
            ]))
            ->setDefaultPaginationData(new PaginationData(
                page: 1,
                perPage: 10,
            ))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'directory_people_show',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'directory.people.title.people_list',
            'translation_domain' => 'directory',
        ]);
    }
}
