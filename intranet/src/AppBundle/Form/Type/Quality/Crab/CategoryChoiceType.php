<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label' => 'crab.fields.category',
                'translation_domain' => 'crab',
                'required' => false,
                'expanded' => false,
                'choices' => [
                    'Assy' => 'Assy',
                    'Test' => 'Test',
                    'QA' => 'QA',
                    'PDI-CSC' => 'PDI-CSC',
                    'PDI-SOL' => 'PDI-SOL',
                    'PDI' => 'PDI',
                    'PDI-INTERNAL' => 'PDI-INTERNAL',
                ],
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
