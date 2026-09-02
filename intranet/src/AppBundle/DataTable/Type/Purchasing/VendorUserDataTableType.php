<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Purchasing;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorUserDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'purchasing/vendor_users';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', ColumnType::class, [
                'label' => 'contacts.fields.id',
                'sort' => true,
            ])
            ->addColumn('firstname', TextColumnType::class, [
                'label' => 'contacts.fields.firstname',
                'sort' => true,
            ])
            ->addColumn('lastname', TextColumnType::class, [
                'label' => 'contacts.fields.lastname',
                'sort' => true,
            ])
            ->addColumn('email', TextColumnType::class, [
                'label' => 'contacts.fields.email',
                'sort' => true,
            ])
            ->addColumn('erpIdentifier', TextColumnType::class, [
                'label' => 'fields.erp_identifier',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('lastLogin', DateTimeColumnType::class, [
                'label' => 'fields.last_login',
                'format' => 'Y-m-d H:i',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'vendor_users_show',
            ]);

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('id', TextFilterType::class)
            ->addFilter('firstname', TextFilterType::class)
            ->addFilter('lastname', TextFilterType::class)
            ->addFilter('email', TextFilterType::class)
            ->addFilter('erpIdentifier', TextFilterType::class)
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('lastLogin', DateRangeFilterType::class, [
                'label' => 'fields.last_login',
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'contacts.vendor_users_title',
            'translation_domain' => 'contacts',
        ]);
    }
}
