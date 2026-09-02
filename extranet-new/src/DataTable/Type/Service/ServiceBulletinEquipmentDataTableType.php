<?php

declare(strict_types=1);

namespace App\DataTable\Type\Service;

use App\DataTable\Action\Type\ShowButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Light data table for the equipments linked to a service bulletin.
 *
 * Fed with an in-memory array of {@see \App\Sdk\Resource\ServiceBulletinEquipment} (handled by
 * Kreyu's ArrayProxyQuery), so it only exposes the few fields available on the legacy equipment
 * record and defines no API-backed filters or search.
 */
#[AutoconfigureTag(name: 'kreyu_data_table.type')]
class ServiceBulletinEquipmentDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('serialNumber', TextColumnType::class, [
                'label' => 'calibration_tools.tools.fields.serialNumber',
                'sort' => true,
            ])
            ->addColumn('model', TextColumnType::class, [
                'label' => 'display.table.vwc_wc.header.model',
                'sort' => true,
            ])
            ->addColumn('type', TextColumnType::class, [
                'label' => 'display.table.vwc.headers.type',
                'sort' => true,
            ])
            ->addColumn('customerName', TextColumnType::class, [
                'label' => 'display.table.vwc_wc.header.customer',
                'sort' => true,
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray(['serialNumber' => 'asc']));
        $builder->setDefaultPaginationData(new PaginationData(page: 1, perPage: 10));

        $builder->addRowAction('show', ShowButtonActionType::class, [
            'route' => 'equipment:show_by_serial_number',
            'key' => 'serialNumber',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'Equipments',
            'translation_domain' => 'messages',
        ]);
    }
}
