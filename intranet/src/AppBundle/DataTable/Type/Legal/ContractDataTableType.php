<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Legal;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\CategoryColumnType;
use AppBundle\DataTable\Column\Type\CurrencyColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Column\Type\SubCategoryColumnType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DivisionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\RegionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Premise\PremiseFilterType;
use AppBundle\DataTable\Filter\Type\Finance\CurrencyFilterType;
use AppBundle\DataTable\Filter\Type\Legal\CategoryFilterType;
use AppBundle\DataTable\Filter\Type\Legal\ContractFilterType;
use AppBundle\DataTable\Filter\Type\Legal\SubCategoryFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'contracts';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'sort' => true,
                'route' => 'contract_show',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'customers.fields.status',
                'header_translation_domain' => 'sales_customers',
            ])
            ->addColumn('businessUnit', TextColumnType::class, [
                'label' => 'contact_campaign.fields.business_unit',
                'header_translation_domain' => 'contact_campaign',
                'sort' => false,
                'getter' => static function ($data) {
                    $businessUnits = [];
                    foreach ($data['businessUnits'] as $businessUnit) {
                        $businessUnits[] = $businessUnit['name'];
                    }

                    return implode(', ', $businessUnits);
                },
            ])
            ->addColumn('subCategory', SubCategoryColumnType::class, [
                'label' => 'legal.fields.sub_category',
                'header_translation_domain' => 'legal',
            ])
            ->addColumn('category', CategoryColumnType::class, [
                'label' => 'legal.fields.category',
                'header_translation_domain' => 'legal',
                'property_path' => '[subCategory?][category]',
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'legal.fields.title',
                'header_translation_domain' => 'legal',
            ])
            ->addColumn('externalParty', TextColumnType::class, [
                'label' => 'legal.fields.external_party',
                'header_translation_domain' => 'legal',
            ])
            ->addColumn('value', TextColumnType::class, [
                'label' => 'legal.fields.value',
                'header_translation_domain' => 'legal',
            ])
            ->addColumn('currency', CurrencyColumnType::class, [
                'label' => 'legal.fields.currency',
                'header_translation_domain' => 'legal',
            ])
            ->addColumn('startDate', DateTimeColumnType::class, [
                'label' => 'contract.form.general.start_date.label',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'contract',
            ])
            ->addColumn('expirationDate', DateTimeColumnType::class, [
                'label' => 'service.maintenance_contract.fields.expiration_date',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'service',
            ])
            ->addColumn('jurisdiction', TextColumnType::class, [
                'label' => 'legal.fields.jurisdiction',
                'header_translation_domain' => 'legal',
            ])
            ->addColumn('owner', PeopleTooltipColumnType::class, [
                'label' => 'shopfloor_columns.owner',
                'header_translation_domain' => 'pio',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'visible' => false,
            ])
            ->addColumn('renewalPeriod', TextColumnType::class, [
                'label' => 'legal.fields.renewal_period',
                'header_translation_domain' => 'legal',
                'visible' => false,
            ])
            ->addColumn('renewalUnit', TextColumnType::class, [
                'label' => 'legal.fields.renewal_unit',
                'header_translation_domain' => 'legal',
                'visible' => false,
            ])
            ->addColumn('createdBy', PeopleTooltipColumnType::class, [
                'label' => 'fields.poster',
                'header_translation_domain' => 'messages',
                'visible' => false,
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'contract_show',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('shortDescription', TextFilterType::class, [
                'label' => 'legal.fields.title',
                'translation_domain' => 'legal',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('startDate', DateRangeFilterType::class, [
                'label' => 'contract.form.general.start_date.label',
                'translation_domain' => 'contract',
            ])
            ->addFilter('expirationDate', DateRangeFilterType::class, [
                'label' => 'service.maintenance_contract.fields.expiration_date',
                'translation_domain' => 'service',
            ])
            ->addFilter('renewalPeriod', TextFilterType::class, [
                'label' => 'legal.fields.renewal_period',
                'translation_domain' => 'legal',
            ])
            ->addFilter('renewalUnit', TextFilterType::class, [
                'label' => 'legal.fields.renewal_unit',
                'translation_domain' => 'legal',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'DAY' => 'DAY',
                        'MONTH' => 'MONTH',
                        'YEAR' => 'YEAR',
                    ],
                ],
            ])
            ->addFilter('externalParty', TextFilterType::class, [
                'label' => 'legal.fields.external_party',
                'translation_domain' => 'legal',
            ])
            ->addFilter('createdBy', PeopleFilterType::class, [
                'label' => 'fields.poster',
                'translation_domain' => 'messages',
            ])
            ->addFilter('owner', PeopleFilterType::class, [
                'label' => 'shopfloor_columns.owner',
                'translation_domain' => 'pio',
            ])
            ->addFilter('status', TextFilterType::class, [
                'label' => 'sales_forecasts.fields.status',
                'translation_domain' => 'sales_forecasts',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        '' => '',
                        'ACTIVE' => 'ACTIVE',
                        'EXPIRED' => 'EXPIRED',
                        'ARCHIVED' => 'ARCHIVED',
                    ],
                ],
            ])
            ->addFilter('value', TextFilterType::class, [
                'label' => 'legal.fields.value',
                'translation_domain' => 'legal',
            ])
            ->addFilter('jurisdiction', TextFilterType::class, [
                'label' => 'legal.fields.jurisdiction',
                'translation_domain' => 'legal',
            ])
            ->addFilter('currency', CurrencyFilterType::class, [
                'label' => 'legal.fields.currency',
                'translation_domain' => 'legal',
            ])
            ->addFilter('subCategory', SubCategoryFilterType::class, [
                'label' => 'legal.fields.sub_category',
                'translation_domain' => 'legal',
            ])
            ->addFilter('category', CategoryFilterType::class, [
                'label' => 'legal.fields.category',
                'translation_domain' => 'legal',
                'query_path' => 'subCategory.category',
            ])
            ->addFilter('divisions', DivisionFilterType::class, [
                'label' => 'menu.division.title',
                'translation_domain' => 'messages',
            ])
            ->addFilter('regions', RegionFilterType::class, [
                'label' => 'menu.region.title',
                'translation_domain' => 'messages',
            ])
            ->addFilter('premises', PremiseFilterType::class, [
                'label' => 'directory.premise.title',
                'translation_domain' => 'directory',
            ])
            ->addFilter('businessUnits', BusinessUnitFilterType::class, [
                'label' => 'menu.business_unit.title',
                'translation_domain' => 'messages',
            ])
            ->addFilter('parentContract', ContractFilterType::class, [
                'label' => 'legal.fields.parent_contract',
                'translation_domain' => 'legal',
            ])
            ->addFilter('childContracts', ContractFilterType::class, [
                'label' => 'legal.fields.child_contract',
                'translation_domain' => 'legal',
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
