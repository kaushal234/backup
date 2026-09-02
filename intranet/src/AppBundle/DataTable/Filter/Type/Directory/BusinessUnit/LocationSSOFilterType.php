<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\BusinessUnit;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Directory\Location\LocationSSOAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationSSOFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => LocationSSOAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
