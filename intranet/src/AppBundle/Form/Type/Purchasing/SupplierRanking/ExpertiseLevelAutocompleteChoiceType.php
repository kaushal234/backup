<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpertiseLevelAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'fields.expertise_level',
            'uri' => 'purchasing/supplier_ranking/expertise_levels',
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
