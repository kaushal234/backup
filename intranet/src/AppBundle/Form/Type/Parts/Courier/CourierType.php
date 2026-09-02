<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts\Courier;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourierType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'translation_domain' => 'messages',
                'required' => $options['add'],
            ])
            ->add('url', TextType::class, [
                'label' => 'customers.fields.url',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('parameterName', TextType::class, [
                'label' => 'courier.fields.parameter_name',
                'required' => false,
            ])
        ;

        if ($options['add']) {
            $builder->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'courier',
            'add' => false,
        ]);
    }
}
