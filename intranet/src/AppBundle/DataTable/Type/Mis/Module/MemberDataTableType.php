<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis\Module;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\TemplateActionType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Column\Type\SimpleLinkColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Position\PositionFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ModalActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'modules/third_party_app/%s/members';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('user', PeopleColumnType::class, [
                'label' => 'mis.member.fields.user',
                'header_translation_domain' => 'mis',
                'sort' => 'user.lastname',
            ])
            ->addColumn('businessUnit', SimpleLinkColumnType::class, [
                'label' => 'mis.member.fields.business_unit',
                'property_path' => '[user][businessUnit?][name]',
                'route' => 'directory_business_units_show',
                'property_path_link' => '[user][businessUnit?][id]',
                'sort' => 'user.businessUnit.name',
            ])
            ->addColumn('position', SimpleLinkColumnType::class, [
                'label' => 'mis.member.fields.position',
                'property_path' => '[user][position?][description]',
                'route' => 'directory_positions_show',
                'property_path_link' => '[user][position?][id]',
                'sort' => 'user.position.description',
            ])
            ->addColumn('admin', BooleanColumnType::class, [
                'label' => 'mis.member.fields.admin',
                'sort' => true,
            ])
        ;

        $builder->addRowAction('remove', ModalActionType::class, [
            'label' => '',
            'route' => 'mis_third_party_app_members_remove_confirm',
            'route_params' => static fn (ApiData $member) => [
                'id' => $member->toArray()['thirdPartyApp']['id'],
                'memberId' => $member->toArray()['id'],
            ],
            'icon' => 'fa7-solid:trash',
            'variant' => 'danger',
        ]);

        $builder->addRowAction('promoteDemote', TemplateActionType::class, [
            'template_path' => 'mis/modules/third_party_app/members/partial/promote_action.html.twig',
        ]);

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });
        $builder->addFilter('firstname', TextFilterType::class, [
            'query_path' => 'user.firstname',
        ]);
        $builder->addFilter('lastname', TextFilterType::class, [
            'query_path' => 'user.lastname',
        ]);
        $builder->addFilter('businessUnit', BusinessUnitFilterType::class, [
            'query_path' => 'user.businessUnit',
            'form_options' => ['multiple' => true],
        ]);
        $builder->addFilter('position', PositionFilterType::class, [
            'query_path' => 'user.position',
            'form_options' => ['multiple' => true],
        ]);
        $builder->addFilter('admin', BooleanFilterType::class);

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class, [
                'extra_query_parameters' => [
                    'normalizationGroupsOverride' => [
                        'member:export',
                        'people:export',
                    ],
                ],
            ])
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'normalizationGroupsOverride' => [
                        'member:export',
                        'people:export',
                    ],
                    'columns' => 'user.id,user,user.username,user.businessUnit,user.position,admin',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'Members',
            'translation_domain' => 'mis',
        ]);
    }
}
