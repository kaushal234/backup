<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\Form\Type\Airport\AirportAutocompleteType;
use App\Sdk\Resource\Airport;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirportFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => AirportAutocompleteType::class,
                'value_extractor' => static fn (Airport $airport) => $airport->code,
                'active_filter_formatter' => static function (FilterData $filterData) {
                    /** @var Airport|array<Airport>|null $resource */
                    $resource = $filterData->getValue();

                    if (null === $resource) {
                        return null;
                    }

                    $formatAirport = static fn (Airport $airport): string => \sprintf('%s - %s', $airport->code, $airport->city);

                    if (\is_array($resource)) {
                        if ([] === $resource) {
                            return null;
                        }

                        return implode(', ', array_map($formatAirport, $resource));
                    }

                    return $formatAirport($resource);
                },
            ])
        ;
    }
}
