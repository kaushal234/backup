<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Task;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Action\Type\TooltipButtonActionType;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\ModuleColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Module\ModuleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TaskDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'sort' => true,
                'route' => static function (object $data) {
                    return match ($data['@type']) {
                        'Task', 'RenewGuestUser', 'PartNumberTask' => 'task_show',
                        'TroubleTicket' => 'trouble_ticket_show',
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
                'routeAsCallable' => true,
            ])
            ->addColumn('module', ModuleColumnType::class, [
                'label' => 'task.fields.module',
                'header_translation_domain' => 'task',
                'sort' => 'module.name',
            ])
            ->addColumn('referenceId', LinkColumnType::class, [
                'getter' => static function ($data) {
                    return $data['referenceId'] ?? null;
                },
                'href' => function (?int $referenceId, ApiData $task): ?string {
                    if (null === $referenceId) {
                        return null;
                    }
                    $route = $task['module']['frontEndRoute'] ?? null;

                    return $route ? $this->urlGenerator->generate($route, ['id' => $referenceId]) : null;
                },
            ])
            ->addColumn('createdBy', PeopleColumnType::class, [
                'label' => 'task.fields.created_by',
                'header_translation_domain' => 'task',
                'sort' => 'createdBy.lastname',
            ])
            ->addColumn('createdAt', DateColumnType::class, [
                'label' => 'task.fields.created_at',
                'header_translation_domain' => 'task',
                'sort' => true,
            ])
            ->addColumn('startedAt', DateColumnType::class, [
                'label' => 'task.fields.started_date',
                'header_translation_domain' => 'task',
                'getter' => static function ($data) {
                    return $data['startedAt'] ?? null;
                },
                'sort' => true,
            ])
            ->addColumn('dueDate', LabelColumnType::class, [
                'label' => 'task.fields.due_date',
                'getter' => static function (ApiData $data) {
                    return !empty($data['dueDate']) ? (new \DateTime($data['dueDate']))->format('d.m.Y') : null;
                },
                'header_translation_domain' => 'task',
                'sort' => true,
                'label_classes' => static function (string $value) {
                    $dueDate = (new \DateTime($value))->setTime(0, 0);
                    $now = (new \DateTime('now'))->setTime(0, 0);
                    $status = match (true) {
                        $now < $dueDate => 'success',
                        $now > $dueDate => 'danger',
                        default => 'warning',
                    };

                    return [$dueDate->format('d.m.Y') => $status];
                },
            ])
            ->addColumn('rescheduleDate', LabelColumnType::class, [
                'label' => 'task.fields.reschedule_date',
                'getter' => static function (ApiData $data) {
                    return !empty($data['rescheduleDate']) ? (new \DateTime($data['rescheduleDate']))->format('d.m.Y') : null;
                },
                'header_translation_domain' => 'task',
                'sort' => true,
                'label_classes' => static function (?string $value) {
                    if (null !== $value) {
                        $rescheduleDate = (new \DateTime($value))->setTime(0, 0);
                        $now = (new \DateTime('now'))->setTime(0, 0);
                        $status = match (true) {
                            $now < $rescheduleDate => 'success',
                            $now > $rescheduleDate => 'danger',
                            default => 'warning',
                        };

                        return [$rescheduleDate->format('d.m.Y') => $status];
                    }

                    return null;
                },
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'fields.short-description',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('status', LabelColumnType::class, [
                'label' => 'task.fields.status',
                'header_translation_domain' => 'task',
                'label_classes' => [
                    'PENDING' => 'default',
                    'PENDING MOO/GKU' => 'default',
                    'IN PROGRESS' => 'info',
                    'AWAITING USER' => 'primary',
                    'MOO/GKU AWAITING USER' => 'primary',
                    'CLOSED' => 'danger',
                    'SOLVED' => 'danger',
                    'NOT AN ISSUE' => 'danger',
                    'ALREADY RAISED' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('indiceFactor', LabelColumnType::class, [
                'label' => 'task.fields.indice_factor',
                'header_translation_domain' => 'task',
                'label_classes' => [
                    'IF 1' => 'default',
                    'IF 10' => 'info',
                    'IF 100' => 'primary',
                    'IF 1000' => 'warning',
                    'IF 10000' => 'danger',
                ],
                'sort' => true,
            ]);
        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => static function (object $data) {
                    return match ($data['@type']) {
                        'Task', 'RenewGuestUser', 'PartNumberTask' => 'task_show',
                        'TroubleTicket' => 'trouble_ticket_show',
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
            ])
            ->addRowAction('Comment', TooltipButtonActionType::class, [
                'label' => false,
                'icon' => 'fa7-solid:comment',
                'tooltip_title' => static function (ApiData $task): string {
                    return $task['lastComment'] ?? '';
                },
                'href' => function (ApiData $data): string {
                    return match ($data['@type']) {
                        'Task', 'RenewGuestUser', 'PartNumberTask' => $this->urlGenerator->generate('task_comment', ['id' => $data->getIriId()]),
                        'TroubleTicket' => $this->urlGenerator->generate('trouble_ticket_comment', ['id' => $data->getIriId()]),
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
            ])
            ->addRowAction('Pause', ButtonActionType::class, [
                'label' => false,
                'icon' => 'fa7-solid:pause',
                'variant' => 'warning',
                'href' => function (ApiData $data): string {
                    return match ($data['@type']) {
                        'Task', 'PartNumberTask' => match ($data['status']) {
                            'PAUSE' => $this->urlGenerator->generate('task_comment', ['id' => $data->getIriId()]),
                            default => $this->urlGenerator->generate('task_pause', ['id' => $data->getIriId()]),
                        },
                        'TroubleTicket', 'RenewGuestUser' => '',
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
                'visible' => static function (object $data): bool {
                    return match ($data['@type']) {
                        'Task' => true,
                        'TroubleTicket', 'RenewGuestUser', 'PartNumberTask' => false,
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
            ])
            ->addRowAction('Close', ButtonActionType::class, [
                'label' => false,
                'icon' => 'fa7-solid:close',
                'variant' => 'danger',
                'href' => function (ApiData $data): string {
                    return match ($data['@type']) {
                        'Task', 'PartNumberTask' => $this->urlGenerator->generate('task_close', ['id' => $data->getIriId()]),
                        'TroubleTicket' => $this->urlGenerator->generate('trouble_ticket_close', ['id' => $data->getIriId()]),
                        'RenewGuestUser' => '',
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
            ])
            ->addRowAction('Transfer', ButtonActionType::class, [
                'label' => false,
                'icon' => 'fa7-solid:arrow-right',
                'variant' => 'info',
                'href' => function (ApiData $task): string {
                    return $this->urlGenerator->generate('task_transfer', ['id' => $task->getIriId()]);
                },
                'visible' => static function (object $data): bool {
                    return match ($data['@type']) {
                        'Task', 'PartNumberTask' => true,
                        'TroubleTicket', 'RenewGuestUser' => false,
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
            ])
            ->addRowAction('Reschedule', ButtonActionType::class, [
                'label' => false,
                'icon' => 'fa7-solid:calendar',
                'variant' => 'info',
                'href' => function (ApiData $task): string {
                    return $this->urlGenerator->generate('task_reschedule', ['id' => $task->getIriId()]);
                },
                'visible' => static function (object $data): bool {
                    return match ($data['@type']) {
                        'Task', 'PartNumberTask' => true,
                        'TroubleTicket', 'RenewGuestUser' => false,
                        default => throw new \LogicException('Unhandled type: '.$data['@type']),
                    };
                },
            ])
        ;

        // Simple search top right, using 'q' parameter of ApiPlatform
        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->addFilter('createdAt', DateRangeFilterType::class, [
            'label' => 'fields.created_at',
            'translation_domain' => 'messages',
        ]);

        $builder->addFilter('dueDate', DateRangeFilterType::class, [
            'label' => 'fields.due_date',
            'translation_domain' => 'messages',
        ]);

        $builder->addFilter('module', ModuleFilterType::class, [
            'label' => 'mis_project.fields.module',
            'translation_domain' => 'mis_project',
            'form_options' => ['multiple' => true],
        ]);

        $builder->addFilter('rescheduleDate', DateRangeFilterType::class, [
            'label' => 'task.fields.reschedule_date',
            'translation_domain' => 'task',
        ]);

        $builder->addFilter('indiceFactor', TextFilterType::class, [
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
        $builder->addFilter('status', TextFilterType::class, [
            'form_type' => SelectFormType::class,
            'form_options' => [
                'choices' => [
                    'PENDING' => 'PENDING',
                    'IN PROGRESS' => 'IN PROGRESS',
                    'CLOSED' => 'CLOSED',
                    'PAUSE' => 'PAUSE',
                ],
                'multiple' => true,
            ],
        ]);
        $builder->addFilter('assignee', PeopleFilterType::class, [
            'label' => 'task.fields.assignee',
            'translation_domain' => 'task',
            'form_options' => ['multiple' => true],
        ]);
        $builder->addFilter('createdBy', PeopleFilterType::class, [
            'label' => 'task.fields.created_by',
            'translation_domain' => 'task',
            'form_options' => ['multiple' => true],
        ]);
        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,module,type,referenceId,createdBy,assignee,createdAt,dueDate,rescheduleDate,status,indiceFactor,shortDescription,description,escalationTrigger',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => '',
            'translation_domain' => 'mis',
        ]);
    }
}
