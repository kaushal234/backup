<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->addNormalizer('filters', static fn (Options $options, $value) => array_merge($value, ['acls.group.name' => ['ROLE_AST', 'ROLE_CSS', 'ROLE_CSTL', 'ROLE_CSM']]));
    }

    public function getParent(): string
    {
        return PeopleChoiceType::class;
    }
}
