<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SparePartsHubChoiceType extends AbstractLocationChoiceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.sparePartsHub' => true,
                ],
            ]);
    }

    public function getParent(): string
    {
        return LocationAutocompleteChoiceType::class;
    }
}
