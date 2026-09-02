<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use AppBundle\Form\Type\Common\SelectFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportanceFactorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'fields.ifactor',
            'translation_domain' => 'messages',
            'choice_translation_domain' => false,
            'choices' => [
                '1' => 'IF1',
                '10' => 'IF10',
                '100' => 'IF100',
                '1000' => 'IF1000',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
