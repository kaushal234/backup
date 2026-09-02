<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\LeaverStatusFilterType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleLeaversListDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'people';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('plannedDisableAt', DateColumnType::class, [
                'label' => 'directory.people.fields.planned_disable_at',
                'sort' => 'disabledAt',
            ])
            ->addColumn('disabledAt', DateColumnType::class, [
                'label' => 'directory.people.fields.disabled_at',
                'sort' => 'disabledAt',
            ])
            ->addColumn('updateTasks', TemplateColumnType::class, [
                'label' => 'mis.update_task.title',
                'header_translation_domain' => 'mis',
                'template_path' => 'human_resources/people_lifecycle/update_tasks_modal.html.twig',
                'getter' => static fn (ApiData|array $people) => $people,
            ])
            ->addFilter('status', LeaverStatusFilterType::class, [
                'label' => 'directory.people.fields.leaver_status.label',
            ])
            ->addFilter('withOngoingUpdateTasks', BooleanFilterType::class, [
                'label' => 'directory.people.fields.with_ongoing_update_tasks',
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'disabledAt' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'directory.people.title.leavers',
            'translation_domain' => 'directory',
        ]);
    }

    public function getParent(): string
    {
        return PeopleLifecycleDataTableType::class;
    }
}
