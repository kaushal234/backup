<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EVPPeopleChoiceType extends AbstractType
{
    final public const array GROUPS = [
        'ROLE_EVP',
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
                    'normalization_groups_override' => ['people_list'],
                    'acls.group.name' => self::GROUPS,
                ],
            ]);
    }

    public function getParent(): string
    {
        return PeopleAutocompleteChoiceType::class;
    }
}
