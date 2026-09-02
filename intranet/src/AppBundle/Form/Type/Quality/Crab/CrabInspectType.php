<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrabInspectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inspectingComments', TextareaType::class, [
                'label' => 'crab.fields.comments',
                'translation_domain' => 'crab',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'crab.button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'crab',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
