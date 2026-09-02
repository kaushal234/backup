<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Purchasing;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\HtmlTooltipColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseTaskDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'route' => 'task_show',
                'sort' => true,
            ])
            ->addColumn('location', LocationColumnType::class, [
                'label' => 'wms.fields.warehouse',
                'header_translation_domain' => 'wms',
                'sort' => 'location.name',
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'display.table.scar.headers.short_description',
            ])
            ->addColumn('description', HtmlTooltipColumnType::class, [
                'label' => 'Description',
            ])
            ->addColumn('assignee', PeopleTooltipColumnType::class, [
                'label' => 'tasks.assignee',
                'header_translation_domain' => 'messages',
                'sort' => 'assignee.lastname',
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
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'trouble_ticket.fields.created_at',
                'header_translation_domain' => 'trouble_ticket',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('status', LabelColumnType::class, [
                'label' => 'task.fields.status',
                'header_translation_domain' => 'task',
                'label_classes' => [
                    'PENDING' => 'default',
                    'IN PROGRESS' => 'info',
                    'CLOSED' => 'danger',
                ],
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
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'task_show',
            ])
            ->addRowAction('edit', ShowButtonActionType::class, [
                'route' => 'warehouse_task_edit',
                'variant' => 'warning',
                'icon' => 'fa7-solid:pencil',
            ])
            ->addRowAction('comment', ShowButtonActionType::class, [
                'route' => 'task_comment',
                'icon' => 'fa7-solid:comment',
            ])
            ->addRowAction('transfer', ShowButtonActionType::class, [
                'route' => 'task_transfer',
                'variant' => 'info',
                'icon' => 'fa7-solid:arrow-right',
            ])
            ->addRowAction('close', ShowButtonActionType::class, [
                'route' => 'task_close',
                'variant' => 'danger',
                'icon' => 'fa7-solid:close',
            ])
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search): void {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
            ->addFilter('assignee', PeopleFilterType::class, [
                'label' => 'tasks.assignee',
                'query_path' => 'assignee',
            ])
            ->addFilter('location', LocationFilterType::class, [
                'label' => 'warehouse',
                'query_path' => 'location',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('status', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'PENDING' => 'PENDING',
                        'IN PROGRESS' => 'IN PROGRESS',
                        'CLOSED' => 'CLOSED',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('indiceFactor', TextFilterType::class, [
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
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'Warehouse Tasks',
            'translation_domain' => 'messages',
        ]);
    }
}
