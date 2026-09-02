<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Purchasing;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\CountryColumnType;
use AppBundle\DataTable\Column\Type\CurrencyColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\Purchasing\Supplier\SupplierLocationColumnType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\BuyerFilterType;
use AppBundle\DataTable\Filter\Type\Finance\CurrencyFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SupplierDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('code', LinkColumnType::class, [
                'label' => 'supplier.code',
                'sort' => true,
                'href' => fn (string $code, ApiData $supplier): string => $this->urlGenerator->generate('suppliers_show', ['id' => $supplier['id']]),
            ])
            ->addColumn('name', TextColumnType::class, [
                'label' => 'supplier.name',
                'sort' => true,
            ])
            ->addColumn('location', LocationColumnType::class, [
                'label' => 'supplier.master_bu',
                'sort' => true,
            ])
            ->addColumn('country', CountryColumnType::class, [
                'sort' => true,
            ])
            ->addColumn('currency', CurrencyColumnType::class, [
                'sort' => true,
            ])
            ->addColumn('buyFrom', CollectionColumnType::class, [
                'label' => 'supplier.buy_from',
                'entry_type' => SupplierLocationColumnType::class,
                'separator' => '&nbsp;',
                'separator_html' => true,
            ])
            ->addColumn('status', LabelColumnType::class, [
                'label' => 'supplier.status',
                'label_classes' => [
                    'ACTIVE' => 'success',
                    'INACTIVE' => 'danger',
                    'DELETED' => 'danger',
                ],
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('code', TextFilterType::class)
            ->addFilter('name', TextFilterType::class)
            ->addFilter('location', LocationFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('country', CountryFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('currency', CurrencyFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('buyFromBuyer', BuyerFilterType::class, [
                'query_path' => 'buyFrom.buyer',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('buyFromLocation', LocationFilterType::class, [
                'query_path' => 'buyFrom.location',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('status', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'ACTIVE' => 'ACTIVE',
                        'INACTIVE' => 'INACTIVE',
                        'DELETED' => 'DELETED',
                    ],
                    'multiple' => true,
                ],
            ])
        ;

        $builder->setDefaultFiltrationData(FiltrationData::fromArray([
            'status' => ['ACTIVE'],
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'supplier.title',
            'translation_domain' => 'messages',
        ]);
    }
}
