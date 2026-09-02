<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationAreaChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'location_areas',
                'text_key' => '[name]',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
