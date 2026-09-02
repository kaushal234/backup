<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartsNumberByErpAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.part_number',
                'translation_domain' => 'messages',
                'uri' => 'ion/items',
                'search_param_name' => 'itemFilter',
                'min_input_length' => 3,
                'text_key' => null,
                'template' => '{{ item }} {{ itemDescription }}',
                'js_template_result' => '{{ item }} {{ itemDescription }}',
                'js_template_selection' => '{{ item }} {{ itemDescription }}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
