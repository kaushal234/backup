<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\JuridicalLocation;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Directory\JuridicalLocation\JuridicalLocationAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JuridicalLocationFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => JuridicalLocationAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
