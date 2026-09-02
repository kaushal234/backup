<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\Directory\PremiseColumnType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Premise\PremiseFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleListDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'people';

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
            ->addColumn('businessUnit', TextColumnType::class, [
                'label' => 'sidebar.hr.directory.business_unit',
                'header_translation_domain' => 'sidebar',
                'property_path' => '[businessUnit?][name]',
                'sort' => 'businessUnit.name',
            ])
            ->addColumn('premise', PremiseColumnType::class, [
                'property_path' => '[premise?][name]',
                'sort' => 'premise.name',
            ])
            ->addColumn('jobTitle', TextColumnType::class, [
                'label' => 'display.table.representative.headers.title',
                'header_translation_domain' => 'messages',
            ])
            ->addFilter('hidden', BooleanFilterType::class, [
                'label' => 'hidden',
            ])
            ->addFilter('disabled', BooleanFilterType::class, [
                'label' => 'disabled',
            ])
            ->addFilter('businessUnit', BusinessUnitFilterType::class)
            ->addFilter('premise', PremiseFilterType::class)
            ->setDefaultFiltrationData(new FiltrationData([
                'hidden' => new FilterData(value: false),
                'disabled' => new FilterData(value: false),
            ]))
            // Simple search top right, using 'q' parameter of ApiPlatform
           ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
               $query->search($search);
           })
            ->setDefaultSortingData(SortingData::fromArray([
                'lastname' => 'asc',
            ]))
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
