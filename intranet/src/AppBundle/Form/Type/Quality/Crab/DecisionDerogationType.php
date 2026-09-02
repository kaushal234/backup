<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DecisionDerogationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'crab.derogation.decision',
                'required' => true,
            ])
            ->add('accepted', SubmitType::class, [
                'label' => 'crab.derogation.status.accept',
                'attr' => ['class' => 'btn btn-green'],
            ])
            ->add('denied', SubmitType::class, [
                'label' => 'crab.derogation.status.deny',
                'attr' => ['class' => 'btn btn-danger'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'crab',
        ]);
    }
}
