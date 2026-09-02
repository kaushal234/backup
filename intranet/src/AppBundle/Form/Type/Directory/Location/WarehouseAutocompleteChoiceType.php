<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.warehouse' => true,
                ],
            ]);
    }

    public function getParent(): string
    {
        return LocationAutocompleteChoiceType::class;
    }
}
