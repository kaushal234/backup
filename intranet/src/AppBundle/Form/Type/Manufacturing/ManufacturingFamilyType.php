<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Manufacturing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManufacturingFamilyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'catalogue.products.name',
                'translation_domain' => 'catalogue',
            ])
            ->add('testDuration', IntegerType::class, [
                'required' => false,
                'label' => 'manufacturing.family.test_duration',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'manufacturing',
            'csrf_protection' => false,
        ]);
    }
}
