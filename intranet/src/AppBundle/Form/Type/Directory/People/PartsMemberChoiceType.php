<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartsMemberChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->addNormalizer('filters', static fn (Options $options, $value) => array_merge($value, ['acls.group.name' => 'GG_PARTS']));
    }

    public function getParent(): string
    {
        return PeopleChoiceType::class;
    }
}
