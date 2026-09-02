<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Service;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\CountryFilterType;
use AppBundle\DataTable\Filter\Type\Service\ServiceAreaFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirportDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('code', TextColumnType::class, [
                'label' => 'fields.airport_code',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('cityName', TextColumnType::class, [
                'label' => 'settings.address.form.city.label',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('country', TextColumnType::class, [
                'label' => 'menu.country.title',
                'header_translation_domain' => 'messages',
                'getter' => static function (ApiData $data): ?string {
                    return $data['country']['name'] ?? null;
                },
            ])
            ->addColumn('serviceAreas', TextColumnType::class, [
                'label' => 'service_area.fields.service_areas_column',
                'header_translation_domain' => 'service',
                'getter' => static function (ApiData $data): string {
                    return implode(', ', array_map(
                        static fn (array $serviceArea) => $serviceArea['name'],
                        iterator_to_array($data['serviceAreas'] ?? [])
                    ));
                },
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->addFilter('country', CountryFilterType::class, [
                'label' => 'menu.country.title',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('code', TextFilterType::class, [
                'label' => 'fields.airport_code',
                'translation_domain' => 'messages',
            ])
            ->addFilter('hasServiceArea', BooleanFilterType::class, [
                'label' => 'service_area.fields.has_service_area',
                'translation_domain' => 'service',
                'query_path' => 'exists[serviceAreas]',
            ])
            ->addFilter('serviceAreas', ServiceAreaFilterType::class, [
                'label' => 'service_area.fields.service_areas_column',
                'translation_domain' => 'service',
                'form_options' => ['multiple' => true],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'service_area.title.airports_list',
            'translation_domain' => 'service',
        ]);
    }
}
