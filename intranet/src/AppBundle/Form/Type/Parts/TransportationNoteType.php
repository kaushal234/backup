<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use AppBundle\Form\Type\CountryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TransportationNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', CountryChoiceType::class, [
                'placeholder' => 'make_selection',
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:400px',
                ],
            ]);
    }
}
