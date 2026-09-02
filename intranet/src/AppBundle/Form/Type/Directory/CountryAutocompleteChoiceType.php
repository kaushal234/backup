<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'countries',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'template' => '{{ name }}',
                'js_template_result' => 'partial/_country_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
