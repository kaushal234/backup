<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'account.menu.acls',
                'translation_domain' => 'account',
                'uri' => 'groups',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'text_key' => '[name]',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
