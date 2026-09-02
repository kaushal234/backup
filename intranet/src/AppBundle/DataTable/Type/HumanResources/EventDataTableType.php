<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\HumanResources;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\HumanResources\EventController;
use AppBundle\DataTable\Column\Type\CountryColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class EventDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'events';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'directory.department.fields.name',
                'header_translation_domain' => 'directory',
                'sort' => 'name',
            ])
            ->addColumn('country', CountryColumnType::class, [
                'sort' => 'country.name',
            ])
            ->addColumn('startedAt', DateTimeColumnType::class, [
                'label' => 'events.fields.started_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('endedAt', DateTimeColumnType::class, [
                'label' => 'events.fields.ended_at',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('dayOff', BooleanColumnType::class, [
                'label' => 'events.fields.day_off',
                'sort' => true,
            ])
        ;

        $builder
            ->addFilter('startedAt', DateRangeFilterType::class, [
                'label' => 'events.fields.started_at',
                'translation_domain' => 'events',
            ])
            ->addFilter('endedAt', DateRangeFilterType::class, [
                'label' => 'events.fields.ended_at',
                'translation_domain' => 'events',
            ])
            ->addFilter('country', CountryFilterType::class)
            ->addFilter('dayOff', BooleanFilterType::class)
        ;

        if ($options['isGrantedAdmin']) {
            $builder->addRowAction('edit', ButtonActionType::class, [
                'label' => false,
                'href' => function (ApiData $event): string {
                    return $this->urlGenerator->generate('event_edit', [
                        'id' => $event['id'],
                    ]);
                },
                'icon' => 'fa7-solid:pencil',
            ]);
            $builder->addRowAction('remove', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $event): string {
                    return $this->urlGenerator->generate('event_delete', [
                        'id' => $event['id'],
                        '_token' => $this->tokenManager->getToken(EventController::DELETE_TOKEN),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'events.delete.popup.title',
                    'label_description' => 'events.delete.popup.message',
                    'translation_domain' => 'events',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
            ]);
        }

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,name,startedAt,endedAt,country.name,dayOff',
                ],
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'startedAt' => 'asc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'events.title',
            'translation_domain' => 'events',
            'personalization_enabled' => false,
            'isGrantedAdmin' => false,
        ]);
    }
}
