<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales\AircraftCompatibility;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ModalActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AircraftDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'aircraft_compatibility.fields.name',
                'sort' => true,
            ])
            ->addColumn('manufacturer', TextColumnType::class, [
                'label' => 'aircraft_compatibility.fields.manufacturer',
                'sort' => 'manufacturer.name',
                'formatter' => static function (array $manufacturer) {
                    return $manufacturer['name'];
                },
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
        ;

        if ($options['displayButtons']) {
            $builder
                ->addRowAction('edit', ButtonActionType::class, [
                    'href' => function (ApiData $aircraft): string {
                        return $this->urlGenerator->generate('aircraft_edit', ['id' => $aircraft->getIriId()]);
                    },
                    'icon' => 'fa7-solid:pencil-alt',
                    'variant' => 'warning',
                ])
                ->addRowAction('delete', ModalActionType::class, [
                    'href' => function (ApiData $aircraft): string {
                        return $this->urlGenerator->generate('aircraft_delete_confirm', ['id' => $aircraft->getIriId()]);
                    },
                    'icon' => 'fa7-solid:trash-alt',
                    'variant' => 'danger',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'aircraft_compatibility.fields.aircrafts',
            'translation_domain' => 'aircraft_compatibility',
            'displayButtons' => false,
        ]);
    }
}
