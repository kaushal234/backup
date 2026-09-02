<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\NewComerStatusFilterType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleNewComersListDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'people';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('enableAt', DateColumnType::class, [
                'label' => 'directory.people.fields.enable_at',
                'sort' => 'enableAt',
            ])
            ->addColumn('updateTasks', TemplateColumnType::class, [
                'label' => 'mis.update_task.title',
                'header_translation_domain' => 'mis',
                'template_path' => 'human_resources/people_lifecycle/update_tasks_modal.html.twig',
                'getter' => static fn (ApiData|array $people) => $people,
            ])
            ->addFilter('status', NewComerStatusFilterType::class, [
                'label' => 'directory.people.fields.new_comer_status.label',
            ])
            ->addFilter('withOngoingUpdateTasks', BooleanFilterType::class, [
                'label' => 'directory.people.fields.with_ongoing_update_tasks',
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'enableAt' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'directory.people.title.new_comers',
            'translation_domain' => 'directory',
        ]);
    }

    public function getParent(): string
    {
        return PeopleLifecycleDataTableType::class;
    }
}
