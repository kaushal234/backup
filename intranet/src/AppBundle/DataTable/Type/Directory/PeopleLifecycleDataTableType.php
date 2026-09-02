<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DivisionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Position\PositionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Premise\PremiseFilterType;
use AppBundle\DataTable\Filter\Type\Mis\SupportTeamFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleLifecycleDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'people';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('people_line_tooltip', PeopleTooltipColumnType::class, [
                'label' => 'directory.user.fields.username',
                'getter' => static fn (ApiData|array $people) => $people,
            ])
            ->addColumn('businessUnit', TextColumnType::class, [
                'label' => 'sidebar.hr.directory.business_unit',
                'header_translation_domain' => 'sidebar',
                'property_path' => '[businessUnit?][name]',
                'sort' => 'businessUnit.name',
            ])
            ->addColumn('position', TextColumnType::class, [
                'label' => 'sidebar.hr.directory.position',
                'header_translation_domain' => 'sidebar',
                'property_path' => '[position?][description]',
                'sort' => 'position.description',
            ])
            ->addColumn('jobTitle', TextColumnType::class, [
                'label' => 'display.table.representative.headers.title',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('supportTeam', TextColumnType::class, [
                'label' => 'directory.premise.fields.support_team',
                'header_translation_domain' => 'directory',
                'sort' => 'premise.supportTeam.name',
                'property_path' => '[premise?][supportTeam?][name]',
            ])

            ->addFilter('lastname', TextFilterType::class)
            ->addFilter('firstname', TextFilterType::class)
            ->addFilter('position', PositionFilterType::class)
            ->addFilter('businessUnit', BusinessUnitFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('premise', PremiseFilterType::class)
            ->addFilter('division', DivisionFilterType::class, [
                'query_path' => 'businessUnit.region.subDivision.division',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('supportTeam', SupportTeamFilterType::class, [
                'query_path' => 'premise.supportTeam',
                'form_options' => ['multiple' => true],
            ])

            // Simple search top right, using 'q' parameter of ApiPlatform
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'directory_people_show',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }
}
