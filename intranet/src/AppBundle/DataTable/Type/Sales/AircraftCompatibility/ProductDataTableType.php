<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales\AircraftCompatibility;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\Sales\Catalog\ProductFamilyColumnType;
use AppBundle\DataTable\Column\Type\Sales\Catalog\ProductTypeColumnType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'catalogue.products.product',
                'sort' => true,
            ])
            ->addColumn('family', ProductFamilyColumnType::class, [
                'sort' => 'family.name',
            ])
            ->addColumn('productType', ProductTypeColumnType::class, [
                'property_path' => '[family?][productType?]',
                'sort' => 'family.productType.englishName',
            ])
        ;

        $builder
            // Simple search top right, using 'q' parameter of ApiPlatform
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'sales_catalogue_product_show',
                'property_path_link' => '@id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'catalogue.family.products',
            'translation_domain' => 'catalogue',
        ]);
    }
}
