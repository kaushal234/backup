<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Todo: This FormType should replace PeopleChoiceType.
 */
class PeopleAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'home.quick_search.people_label',
                'uri' => 'people/search',
                'query' => [
                    'hidden' => false,
                    'disabled' => false,
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                ],
                'template' => '{{lastname}} {{firstname}}',
                'js_template_result' => 'directory/people/partial/_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
