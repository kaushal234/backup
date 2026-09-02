<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'translation_domain' => 'messages',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'demo',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forex',
        ]);
    }
}
