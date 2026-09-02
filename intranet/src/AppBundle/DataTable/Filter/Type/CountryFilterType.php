<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Directory\CountryAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CountryAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
