<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyerChoiceType extends AbstractType
{
    final public const GROUPS = [
        'ROLE_BYR',
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->addNormalizer('filters', static fn (Options $options, $value) => array_merge($value, ['acls.group.name' => self::GROUPS]));
    }

    public function getParent(): string
    {
        return PeopleChoiceType::class;
    }
}
