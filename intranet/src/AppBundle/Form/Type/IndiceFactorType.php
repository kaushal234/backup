<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndiceFactorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'fields.ifactor',
            'translation_domain' => 'messages',
            'choice_translation_domain' => false,
            'choices' => [
                'IF 1' => 'IF 1',
                'IF 10' => 'IF 10',
                'IF 100' => 'IF 100',
                'IF 1000' => 'IF 1000',
            ],
            'expanded' => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
