<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.position.name',
                'uri' => 'positions',
                'text_key' => '[description]',
                'query' => [
                    'order' => [
                        'description' => 'ASC',
                    ],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
