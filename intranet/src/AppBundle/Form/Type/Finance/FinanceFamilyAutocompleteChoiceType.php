<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinanceFamilyAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'finance/finance_families',
                'text_key' => 'name',
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
