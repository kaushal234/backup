<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleAdvancedChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'people',
                'query' => [
                    'hidden' => false,
                    'disabled' => false,
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                ],
                'template' => '{{ lastname }} {{ firstname }}',
                'js_template_result' => 'directory/people/partial/_advanced_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return PeopleAutocompleteChoiceType::class;
    }
}
