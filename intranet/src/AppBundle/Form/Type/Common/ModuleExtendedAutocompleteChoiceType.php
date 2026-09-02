<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleExtendedAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => '',
                'uri' => 'extendeds',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'text_key' => '[name]',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
