<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpertiseLevelChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
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
