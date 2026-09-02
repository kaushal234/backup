<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SSOChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.sso' => true,
                ],
                'js_template_result' => 'directory/location/partial/_sso_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return LocationAutocompleteChoiceType::class;
    }
}
