<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FactoryChoiceType extends AbstractLocationChoiceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.factory' => true,
                    'state.hidden' => false,
                ],
            ]);
    }

    public function getParent(): string
    {
        return LocationAutocompleteChoiceType::class;
    }
}
