<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Service;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\Service\ServiceArea\DeleteController;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\LongListColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ServiceAreaDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager)
    {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'service_area.fields.service_area_name',
                'header_translation_domain' => 'service',
                'sort' => true,
            ])
            ->addColumn('representative', PeopleColumnType::class, [
                'label' => 'service_area.fields.service_area_representative',
                'header_translation_domain' => 'service',
                'sort' => 'representative.lastname',
            ])
            ->addColumn('airport', LongListColumnType::class, [
                'label' => 'service_area.fields.airports',
                'header_translation_domain' => 'service',
                'property_path' => 'airports',
                'item_formatter' => static fn (array $airport): string => \sprintf('%s (%s)', $airport['code'], $airport['cityName']),
                'item_sort_key' => 'cityName',
                'group_by' => 'country.name',
                'limit' => 8,
            ])
            ->addRowAction('edit', ShowButtonActionType::class, [
                'route' => 'service_area_edit',
                'variant' => 'warning',
                'icon' => 'fa7-solid:pencil',
            ])
            ->addRowAction('delete', ShowButtonActionType::class, [
                'route' => 'service_area_edit',
                'variant' => 'warning',
                'icon' => 'fa7-solid:pencil',
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $category): string {
                    return $this->urlGenerator->generate('service_areas_delete', [
                        'id' => $category->getIriId(),
                        '_token' => $this->tokenManager->getToken(DeleteController::DELETE_TOKEN),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'confirmation',
                    'label_description' => 'modal_messages.confirm_delete',
                    'translation_domain' => 'messages',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->addFilter('airports', AirportFilterType::class, [
                'label' => 'service_area.fields.airports',
                'translation_domain' => 'service',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('representative', PeopleFilterType::class, [
                'label' => 'service_area.fields.service_area_representative',
                'translation_domain' => 'service',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('country', CountryFilterType::class, [
                'label' => 'service_area.fields.countries',
                'translation_domain' => 'service',
                'form_options' => ['multiple' => true],
                'query_path' => 'airports.country',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'menu.service_areas.title',
            'translation_domain' => 'messages',
        ]);
    }
}
