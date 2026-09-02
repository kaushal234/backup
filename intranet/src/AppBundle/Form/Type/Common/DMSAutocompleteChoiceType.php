<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DMSAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => '',
                'uri' => 'dms',
                'query' => [
                    'order' => [
                        'title' => 'ASC',
                    ],
                ],
                'normalization_groups_override' => ['document_list', 'expose_legacy'],
                'template' => '{{ id }} - {{ title }}',
                'js_template_result' => 'partial/_dms_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
