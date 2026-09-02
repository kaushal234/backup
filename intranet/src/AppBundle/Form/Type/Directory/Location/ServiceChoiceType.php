<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.serviceHub' => true,
                ],
            ]);
    }

    public function getParent(): string
    {
        return LocationAutocompleteChoiceType::class;
    }
}
