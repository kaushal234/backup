<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartsNumberAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'ion/item_monologistics',
                'search_param_name' => 'itemCode[like]',
                'search_wildcard' => '%',
                'query' => [
                    'selection' => [
                        'description',
                        'itemCode',
                    ],
                ],
                'template' => '{{ partNumber }} {{ description }}',
                'js_template_result' => '{{ itemCode }} {{ description }}',
                'js_template_selection' => '{{ itemCode }} {{ description }}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
