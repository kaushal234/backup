<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\Form\Type\CountryAutocompleteType;
use App\Sdk\Resource\Country;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CountryAutocompleteType::class,
                'value_extractor' => static fn (Country $country) => $country->name,
                'active_filter_formatter' => static function (FilterData $filterData) {
                    /** @var Country|array<Country>|null $resource */
                    $resource = $filterData->getValue();

                    if (null === $resource) {
                        return null;
                    }

                    $format = static fn (Country $country): string => $country->name;

                    if (\is_array($resource)) {
                        if ([] === $resource) {
                            return null;
                        }

                        return implode(', ', array_map($format, $resource));
                    }

                    return $format($resource);
                },
            ])
        ;
    }
}
