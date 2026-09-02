<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis\GuestUser;

use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\BusinessUnitColumnType;
use AppBundle\DataTable\Column\Type\Directory\PremiseColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Premise\PremiseFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuestUserDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'guest_users';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('lastname', TextColumnType::class, [
                'label' => 'contacts.fields.lastname',
                'header_translation_domain' => 'contacts',
                'sort' => true,
            ])
            ->addColumn('firstname', TextColumnType::class, [
                'label' => 'contacts.fields.firstname',
                'header_translation_domain' => 'contacts',
                'sort' => true,
            ])
            ->addColumn('businessUnit', BusinessUnitColumnType::class, [
                'sort' => 'businessUnit.name',
            ])
            ->addColumn('premise', PremiseColumnType::class, [
                'sort' => 'premise.name',
            ])
            ->addColumn('supervisor', PeopleColumnType::class, [
                'sort' => 'supervisor.lastname',
                'label' => 'directory.people.fields.supervisor',
                'header_translation_domain' => 'directory',
            ])
            ->addColumn('needsCollaborationAccess', BooleanColumnType::class, [
                'label' => 'mis.guest_user.fields.needs_collaboration_access',
            ])
            ->addColumn('disabled', BooleanColumnType::class, [])
            ->addColumn('modules', CollectionColumnType::class, [
                'label' => 'task.fields.module',
                'header_translation_domain' => 'task',
                'entry_type' => TextColumnType::class,
                'property_path' => 'modules',
                'entry_options' => [
                    'formatter' => static function ($module): string {
                        return $module['name'];
                    },
                ],
            ])
            ->addColumn('email', TextColumnType::class, [
                'label' => 'contacts.fields.email',
                'header_translation_domain' => 'contacts',
            ])
            ->addColumn('updateTasksRatio', TextColumnType::class, [
                'label' => 'mis.guest_user.fields.update_tasks_ratio',
                'header_translation_domain' => 'mis',
                'property_path' => 'updateTasks',
                'sort' => false,
                'formatter' => static function (?array $updateTasks): string {
                    if (empty($updateTasks)) {
                        return '0/0';
                    }

                    $total = \count($updateTasks);
                    $open = \count(array_filter(
                        $updateTasks,
                        static function ($task): bool {
                            $done = \is_array($task) ? ($task['done'] ?? true) : ($task->done ?? true);

                            return false === $done;
                        }
                    ));

                    $percentage = round(($open / $total) * 100);

                    return \sprintf('%d/%d (%d%%)', $open, $total, $percentage);
                },
            ])
            ->addFilter('hidden', BooleanFilterType::class, [
                'label' => 'hidden',
            ])
            ->addFilter('disabled', BooleanFilterType::class)
            ->addFilter('businessUnit', BusinessUnitFilterType::class)
            ->addFilter('premise', PremiseFilterType::class)
            ->addFilter('supervisor', PeopleFilterType::class, [
                'label' => 'directory.people.fields.supervisor',
                'translation_domain' => 'directory',
            ])
            // Simple search top right, using 'q' parameter of ApiPlatform
           ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
               $query->search($search);
           })
            ->setDefaultSortingData(SortingData::fromArray([
                'lastname' => 'asc',
            ]))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'mis_guest_user_show',
            ]);

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => implode(',', [
                        'id',
                        'fullName',
                        'email',
                        'businessUnit',
                        'premise',
                        'supervisor',
                        'needsCollaborationAccess',
                        'enableAt',
                        'plannedDisableAt',
                        'hidden',
                        'disabled',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'mis.guest_user.title',
            'translation_domain' => 'mis',
        ]);
    }
}
