<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationSSOAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.location.name',
                'uri' => 'locations',
                'text_key' => 'name',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.sso' => 1,
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
