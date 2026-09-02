<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis\Project;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\HtmlTooltipColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Column\Type\PhaseDateColumnType;
use AppBundle\DataTable\Column\Type\RegionColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\RegionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Mis\TagFilterType;
use AppBundle\DataTable\Filter\Type\Module\ModuleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\NumberColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'sort' => true,
                'route' => 'mis_project_show',
            ])
            ->addColumn('name', TextColumnType::class, [
                'label' => 'mis_project.fields.name',
                'sort' => false,
            ])
            ->addColumn('region', RegionColumnType::class, [
                'sort' => 'region.name',
            ])
            ->addColumn('tags', CollectionColumnType::class, [
                'label' => 'fields.tags',
                'header_translation_domain' => 'messages',
                'property_path' => 'tags',
                'entry_type' => TemplateColumnType::class,
                'entry_options' => [
                    'template_path' => 'mis/project/partial/cell/_tags_datatable.html.twig',
                    'template_vars' => static function (array $tag): array {
                        return ['name' => $tag['name']];
                    },
                ],
                'separator' => ' ',
            ])
            ->addColumn('projectManager', PeopleColumnType::class, [
                'label' => 'mis_project.fields.project_manager',
                'header_translation_domain' => 'mis_project',
                'sort' => 'projectManager.lastname',
            ])
            ->addColumn('misOwner', PeopleColumnType::class, [
                'label' => 'mis_project.fields.mis_owner',
                'header_translation_domain' => 'mis_project',
                'sort' => 'misOwner.lastname',
            ])
            ->addColumn('indicesFactor', LabelColumnType::class, [
                'label' => 'mis_project.fields.indices_factor',
                'label_classes' => [
                    'IF 1' => 'default',
                    'IF 10' => 'info',
                    'IF 100' => 'primary',
                    'IF 1000' => 'warning',
                    'IF 10000' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('startedAt', PhaseDateColumnType::class, [
                'label' => 'mis_project.fields.started_at',
                'sort' => 'startedAt',
                'visible' => false,
            ])
            ->addColumn('estimatedHours', TextColumnType::class, [
                'label' => 'mis_project.fields.estimated_hours',
                'sort' => false,
            ])
            ->addColumn('status', TemplateColumnType::class, [
                'label' => 'mis_project.fields.current_phase',
                'sort' => false,
                'template_path' => 'mis/project/partial/cell/_status_datatable.html.twig',
            ])
            ->addColumn('activePhaseEstimatedClosureAt', PhaseDateColumnType::class, [
                'label' => 'mis_project.fields.phase_due_date',
                'sort' => 'activePhaseEstimatedClosureAt',
            ])
            ->addColumn('activePhaseRevisedClosureAt', PhaseDateColumnType::class, [
                'label' => 'mis_project.fields.revised_phase_due_date',
                'sort' => 'activePhaseRevisedClosureAt',
            ])
            ->addColumn('dueDate', PhaseDateColumnType::class, [
                'label' => 'mis_project.fields.due_date',
                'sort' => 'dueDate',
            ])
            ->addColumn('revisedDueDate', PhaseDateColumnType::class, [
                'label' => 'mis_project.fields.due_date_revised',
                'sort' => 'revisedDueDate',
            ])
            ->addColumn('numberOfOpenTasks', TextColumnType::class, [
                'label' => 'mis_project.fields.open_tasks',
                'getter' => static fn ($row) => $row['activePhase'],
                'formatter' => static function ($value) {
                    return \count(array_filter($value['tasks'], static fn ($task) => 'CLOSED' !== $task['status']));
                },
            ])
            ->addColumn('numberOfCloseTasks', NumberColumnType::class, [
                'label' => 'mis_project.fields.close_tasks',
                'value_attr' => [
                    'class' => 'text-center',
                ],
                'getter' => static fn ($row) => $row['activePhase'],
                'formatter' => static function ($value) {
                    return \count(array_filter($value['tasks'], static fn ($task) => 'CLOSED' === $task['status']));
                },
            ])
            ->addColumn('lastComment', HtmlTooltipColumnType::class, [
                'label' => 'mis_project.fields.last_comment',
                'sort' => false,
                'getter' => static function (ApiData $project): ?string {
                    $html = $project['lastComment'] ?? null;
                    $date = $project['lastCommentedAt'] ?? null;

                    if (null === $html || '' === trim($html)) {
                        return null;
                    }
                    $date = mb_substr($date, 0, 10);

                    $plainText = html_entity_decode(strip_tags($html), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

                    return $date
                        ? \sprintf('<strong>%s</strong> : %s', $date, $plainText)
                        : $plainText;
                },
                'header_attr' => [
                    'style' => 'min-width: 400px; max-width: 400px;',
                ],
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'mis_project_show',
            ])
            ->addRowAction('file', ShowButtonActionType::class, [
                'icon' => 'fa7-solid:file',
                'variant' => 'info',
                'route' => 'mis_project_show',
                'extra_params' => [
                    '_fragment' => 'files',
                ],
            ])
            ->addRowAction('log', ShowButtonActionType::class, [
                'icon' => 'fa7-solid:circle-info',
                'variant' => 'info',
                'route' => 'mis_project_show',
                'extra_params' => [
                    '_fragment' => 'projectLogs',
                ],
            ])
        ;

        $builder->addFilter('status', TextFilterType::class, [
            'form_type' => SelectFormType::class,
            'form_options' => [
                'choices' => [
                    'PENDING' => 'PENDING',
                    'PHASE 0' => 'PHASE 0',
                    'PHASE 1' => 'PHASE 1',
                    'PHASE 2' => 'PHASE 2',
                    'PHASE 3' => 'PHASE 3',
                    'PHASE 4' => 'PHASE 4',
                    'CLOSED' => 'CLOSED',
                    'CANCELLED' => 'CANCELLED',
                ],
                'multiple' => true,
            ],
        ]);

        $builder->addFilter('indicesFactor', TextFilterType::class, [
            'label' => 'mis_project.fields.indices_factor',
            'form_type' => SelectFormType::class,
            'form_options' => [
                'choices' => [
                    'IF 1' => 'IF 1',
                    'IF 10' => 'IF 10',
                    'IF 100' => 'IF 100',
                    'IF 1000' => 'IF 1000',
                    'IF 10000' => 'IF 10000',
                ],
                'multiple' => true,
            ],
        ]);

        $builder->addFilter('projectManager', PeopleFilterType::class, [
            'label' => 'mis_project.fields.project_manager',
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('misOwner', PeopleFilterType::class, [
            'label' => 'mis_project.fields.mis_owner',
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('moduleKeyUsers', PeopleFilterType::class, [
            'label' => 'mis_project.fields.module_key_users',
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('misMembers', PeopleFilterType::class, [
            'label' => 'mis_project.fields.mis_members',
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('tags', TagFilterType::class, [
            'label' => 'fields.tags',
            'translation_domain' => 'messages',
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('region', RegionFilterType::class, [
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('module', ModuleFilterType::class, [
            'label' => 'mis_project.fields.module',
            'form_options' => ['multiple' => true],
        ]);

        // Simple search top right, using 'q' parameter of ApiPlatform
        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });
        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,name,region,indicesFactor,projectManager,misOwner,createdAt,startedAt,confidential,module,status,tags,dueDate,revisedDueDate,estimatedHours,revisedEstimatedHours',
                ],
            ])
        ;
        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'MIS Projects',
            'translation_domain' => 'mis_project',
        ]);
    }
}
