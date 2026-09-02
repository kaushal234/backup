<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MISAutocompleteChoiceType extends AbstractType
{
    final public const array GROUPS = [
        'GG_MIS',
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'query' => [
                    'hidden' => false,
                    'disabled' => false,
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                    'acls.group.name' => self::GROUPS,
                ],
            ]);
    }

    public function getParent(): string
    {
        return PeopleAutocompleteChoiceType::class;
    }
}
