<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\FollowedTopics;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Module\ModuleFilterType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FollowedTopicsDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('resourceId', IdLinkColumnType::class, [
                'label' => 'ID',
                'route' => static fn (ApiData $data): string => $data['module']['frontEndRoute'] ?? 'home',
                'routeAsCallable' => true,
                'params' => static fn (ApiData $data): array => ['id' => $data['resourceId']],
            ])
            ->addColumn('module', TextColumnType::class, [
                'label' => 'subscribers.module',
                'formatter' => static fn (?array $module) => $module['name'] ?? null,
            ])
            ->addColumn('user', PeopleColumnType::class, [
                'label' => 'subscribers.user',
                'header_translation_domain' => 'subscribers',
                'sort' => 'user.lastname',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'subscribers.followed_since',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'subscribers.short_description',
            ]);
        $builder
            ->addFilter('user', PeopleFilterType::class, [
                'label' => 'subscribers.user',
                'form_options' => ['multiple' => true],
            ]);
        $builder
            ->setDefaultFiltrationData(FiltrationData::fromArray([
                'user' => [$options['user']],
            ]));
        $builder
            ->addFilter('module', ModuleFilterType::class, [
                'form_options' => [
                    'multiple' => true,
                    'query' => [
                        'order' => ['name' => 'ASC'],
                        // TODO find a solution avoid hardcode list of module
                        'name' => ['DEMO', 'TASK', 'MOM', 'SFR', 'TTS', 'SCAR', 'CRS', 'VWC'],
                    ],
                ],
            ]);
        $builder
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'subscribers.followed_since',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'subscribers.followed_topics',
            'translation_domain' => 'subscribers',
            'user' => null,
        ]);
    }
}
