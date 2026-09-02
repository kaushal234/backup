<?php

declare(strict_types=1);

namespace App\Form\Type\Service;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TechnicianOnCallSatisfactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('execution', ChoiceType::class, [
                'label' => 'extranet.satisfaction.execution',
                'expanded' => true,
                'choices' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ],
            ])
            ->add('responsiveness', ChoiceType::class, [
                'label' => 'extranet.satisfaction.responsiveness',
                'expanded' => true,
                'choices' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ],
            ])
            ->add('communication', ChoiceType::class, [
                'label' => 'extranet.satisfaction.communication',
                'expanded' => true,
                'choices' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ],
            ])
            ->add('attitude', ChoiceType::class, [
                'label' => 'extranet.satisfaction.attitude',
                'expanded' => true,
                'choices' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'extranet.satisfaction.comment',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'resize:vertical; height:300px',
                ],
            ])
        ;
    }
}
