<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Project;

use AppBundle\Form\Type\Common\SelectFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndiceFactorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'IF 1' => 'IF 1',
                'IF 10' => 'IF 10',
                'IF 100' => 'IF 100',
                'IF 1000' => 'IF 1000',
                'IF 10000' => 'IF 10000',
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
