<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'sales/products',
                'text_key' => '[name]',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'normalization_groups_override' => ['product_list'],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
