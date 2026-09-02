<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportanceFactorChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label' => 'ifactor',
                'choice_translation_domain' => false,
                'choices' => [
                    'IF 1' => 'IF 1',
                    'IF 10' => 'IF 10',
                    'IF 100' => 'IF 100',
                    'IF 1000' => 'IF 1000',
                ],
                'placeholder' => '',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
