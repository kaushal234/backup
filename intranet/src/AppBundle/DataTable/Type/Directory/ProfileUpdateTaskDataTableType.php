<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Directory;

use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\BusinessUnitColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\BusinessUnitFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ModalActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUpdateTaskDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'modules/third_party_app/update_tasks';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('thirdPartyApp', TextColumnType::class, [
                'label' => 'mis.update_task.fields.module_name',
                'header_translation_domain' => 'mis',
                'property_path' => '[thirdPartyApp][name]',
                'sort' => 'thirdPartyApp.name',
            ])
            ->addColumn('application', TextColumnType::class, [
                'label' => 'mis.modules.fields.application',
                'header_translation_domain' => 'mis',
                'property_path' => '[thirdPartyApp][application][name]',
                'sort' => 'thirdPartyApp.application.name',
            ])
            ->addColumn('mainAdmin', PeopleColumnType::class, [
                'label' => 'mis.modules.fields.main_admin',
                'header_translation_domain' => 'mis',
                'property_path' => '[thirdPartyApp][mainAdmin]',
                'sort' => 'user.mainAdmin.lastname',
            ])
            ->addColumn('businessUnit', BusinessUnitColumnType::class, [
                'label' => 'mis.update_task.fields.business_unit',
                'header_translation_domain' => 'mis',
                'property_path' => '[user][businessUnit][name]',
                'sort' => 'user.businessUnit.name',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'mis.update_task.fields.created_at',
                'format' => 'Y-m-d H:i',
                'sort' => true,
                'visible' => false,
            ])
            ->addColumn('updatedAt', DateTimeColumnType::class, [
                'label' => 'mis.update_task.fields.updated_at',
                'format' => 'Y-m-d H:i',
                'sort' => true,
                'formatter' => static function (string $value, ApiData $updateTask) {
                    // Return updated at value only when a manual action was made.
                    // To not display the return date of creation by daily command.
                    if (null === $updateTask['updatedBy']) {
                        return null;
                    }

                    return $value;
                },
            ])
            ->addColumn('updatedBy', PeopleColumnType::class, [
                'label' => 'mis.update_task.fields.updated_by',
                'header_translation_domain' => 'mis',
                'sort' => 'updatedBy.lastname',
            ])
            ->addColumn('originType', LabelColumnType::class, [
                'label' => 'mis.update_task.fields.origin_type',
                'sort' => true,
            ])
            ->addColumn('demandType', LabelColumnType::class, [
                'label' => 'mis.update_task.fields.demand_type',
                'label_classes' => [
                    'GRANT_ACCESS' => 'success',
                    'REMOVE_ACCESS' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('status', LabelColumnType::class, [
                'label' => 'mis.update_task.fields.status',
                'getter' => static function (ApiData $updateTask) {
                    return match (true) {
                        $updateTask['done'] && $updateTask['confirmed'] => 'CONFIRMED',
                        $updateTask['done'] && !$updateTask['confirmed'] => 'DENIED',
                        default => 'IN_PROGRESS',
                    };
                },
                'label_classes' => [
                    'CONFIRMED' => 'default',
                    'DENIED' => 'default',
                    'IN_PROGRESS' => 'info',
                ],
            ])
            ->addColumn('comment', TextColumnType::class, [
                'label' => 'mis.update_task.fields.comment',
                'visible' => false,
            ])
        ;

        $builder
            ->addRowAction('confirmed', ModalActionType::class, [
                'visible' => static fn (ApiData $updateTask) => !$updateTask['done'],
                'route' => 'directory_people_update_tasks_confirmed_modal',
                'route_params' => static fn (ApiData $updateTask) => [
                    'id' => Iri::id($options['user']),
                    'updateTaskId' => $updateTask->getIriId(),
                ],
                'variant' => 'info',
                'icon' => 'fa7-solid:check',
            ])
            ->addRowAction('denied', ModalActionType::class, [
                'visible' => static fn (ApiData $updateTask) => !$updateTask['done'],
                'route' => 'directory_people_update_tasks_denied_modal',
                'route_params' => static fn (ApiData $updateTask) => [
                    'id' => Iri::id($options['user']),
                    'updateTaskId' => $updateTask->getIriId(),
                ],
                'variant' => 'danger',
                'icon' => 'fa7-solid:times',
            ])
            ->addRowAction('comment', ButtonActionType::class, [
                'label' => 'mis.update_task.fields.comment',
                'variant' => 'info',
                'icon' => 'fa7-solid:circle-info',
                'visible' => static fn (ApiData $updateTask) => $updateTask['done'] && null !== $updateTask['comment'],
                'attr' => [
                    'data-turbo-prefetch' => 'false',
                ],
                'confirmation' => static fn (ApiData $updateTask) => [
                    'translation_domain' => 'mis',
                    'label_title' => 'mis.update_task.fields.comment',
                    'label_description' => $updateTask['comment'],
                    'label_cancel' => 'OK',
                    'label_confirm' => null,
                ],
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('user', PeopleFilterType::class, [
                'label' => 'mis.update_task.fields.user',
            ])
            ->addFilter('businessUnit', BusinessUnitFilterType::class, [
                'label' => 'mis.update_task.fields.business_unit',
                'query_path' => 'user.businessUnit',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('updatedBy', PeopleFilterType::class, [
                'label' => 'mis.update_task.fields.updated_by',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('originType', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'BUSINESS_UNIT_POSITION' => 'BUSINESS_UNIT_POSITION',
                        'WHITELIST' => 'WHITELIST',
                        'BLACKLIST' => 'BLACKLIST',
                        'USER' => 'USER',
                        'MANUAL' => 'MANUAL',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('demandType', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'GRANT_ACCESS' => 'GRANT_ACCESS',
                        'REMOVE_ACCESS' => 'REMOVE_ACCESS',
                    ],
                    'multiple' => true,
                ],
            ])
            // This use custom api filter to filter on property 'done' and 'confirmed'.
            ->addFilter('status', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'IN_PROGRESS' => 'IN_PROGRESS',
                        'CONFIRMED' => 'CONFIRMED',
                        'DENIED' => 'DENIED',
                    ],
                ],
            ])
            ->addFilter('updatedAt', DateRangeFilterType::class)
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'updatedAt' => 'desc',
        ]));
        $builder->setDefaultFiltrationData(FiltrationData::fromArray([
            'status' => 'IN_PROGRESS',
            'user' => $options['user'],
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'mis.update_task.title',
            'translation_domain' => 'mis',
            'themes' => [
                'datatable_simple_theme.html.twig',
            ],
            'user' => null,
            'filtration_persistence_enabled' => false,
            'sorting_persistence_enabled' => false,
            'pagination_persistence_enabled' => false,
        ]);
    }
}
