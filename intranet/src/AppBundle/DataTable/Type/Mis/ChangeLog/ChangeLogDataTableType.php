<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis\ChangeLog;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\ModuleColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Module\ChangeLogTypeFilterType;
use AppBundle\DataTable\Filter\Type\Module\ModuleFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChangeLogDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'change_logs';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $typeMapping = [
            'build' => 'Internal',
            'ci' => 'Internal',
            'docs' => 'Internal',
            'feat' => 'New',
            'fix' => 'Fix',
            'perf' => 'Performance',
            'refactor' => 'Internal',
            'style' => 'UI',
            'test' => 'Internal',
            'chore' => 'Internal',
            'revert' => 'Internal',
        ];
        $builder
            ->addColumn('module', ModuleColumnType::class, [
                'sort' => 'module.name',
            ])
            ->addColumn('type', LabelColumnType::class, [
                'label' => 'mis.changelog.fields.type',
                'header_translation_domain' => 'mis',
                'sort' => true,
                'text_values' => static function ($value) use ($typeMapping) {
                    if (null === $value) {
                        return null;
                    }
                    $key = mb_strtolower(trim((string) $value));

                    return $typeMapping[$key] ?? 'Internal';
                },
                'template_path' => 'mis/change_logs/partials/cell/_type.html.twig',
                'label_classes' => static function ($value) use ($typeMapping) {
                    if (null === $value) {
                        return null;
                    }
                    $key = mb_strtolower(trim((string) $value));
                    $label = $typeMapping[$key] ?? 'Internal';

                    return match ($label) {
                        'Internal' => 'badge badge-warning text-uppercase',
                        'New' => 'badge badge-success text-uppercase',
                        'Fix' => 'badge badge-primary text-uppercase',
                        'Performance' => 'badge badge-primary text-uppercase',
                        'UI' => 'badge badge-primary text-uppercase',
                    };
                },
            ])
            ->addColumn('date', DateTimeColumnType::class, [
                'label' => 'mis.changelog.fields.date',
                'header_translation_domain' => 'mis',
                'sort' => true,
            ])
            ->addColumn('message', TextColumnType::class, [
                'label' => 'mis.changelog.fields.description',
                'header_translation_domain' => 'mis',
                'sort' => true,
            ])
            ->addColumn('moo', PeopleColumnType::class, [
                'label' => 'mis.modules.fields.moo',
                'header_translation_domain' => 'mis',
                'property_path' => '[module?][operationalOwner]',
                'sort' => 'module.operationalOwner.lastname',
            ])
            ->addColumn('author', PeopleColumnType::class, [
                'label' => 'mis.changelog.fields.author',
                'header_translation_domain' => 'mis',
                'sort' => 'author.lastname',
            ])
            ->addColumn('ticket', LinkColumnType::class, [
                'label' => 'mis.changelog.fields.ticket',
                'header_translation_domain' => 'mis',
                'href' => fn (?int $ticket = null) => $ticket
                    ? $this->urlGenerator->generate('trouble_ticket_show', ['id' => $ticket])
                    : null,
                'sort' => true,
            ])
        ;

        $builder
            ->addFilter('module', ModuleFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('operationalOwner', PeopleFilterType::class, [
                'form_options' => ['multiple' => true],
                'query_path' => 'module.operationalOwner',
            ])
            ->addFilter('author', PeopleFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('date', DateRangeFilterType::class, [
                'translation_domain' => 'messages',
            ])
            ->addFilter('type', ChangeLogTypeFilterType::class, [
                'label' => 'mis.changelog.fields.type',
                'translation_domain' => 'mis',
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'date' => 'desc',
            ]))
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'module,type,date,message,module.operationalOwner,author,ticket',
                ],
            ])
        ;

        $builder
            ->addRowAction('update', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $changeLog): string {
                    return $this->urlGenerator->generate('mis_change_logs_edit', [
                        'id' => $changeLog->getIriId(),
                    ]);
                },
                'icon' => 'fa7-solid:edit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'mis.changelog.title',
            'translation_domain' => 'mis',
        ]);
    }
}
