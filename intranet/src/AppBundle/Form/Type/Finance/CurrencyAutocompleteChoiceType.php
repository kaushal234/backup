<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'finance/currencies',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'template' => '{{ name }}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
