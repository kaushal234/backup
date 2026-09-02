<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditLimitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'customers.fields.type',
                'translation_domain' => 'sales_customers',
                'choice_translation_domain' => false,
                'choices' => [
                    'FACTOR' => 'FACTOR',
                    'INTERNAL UNITS' => 'INTERNAL UNITS',
                    'INTERNAL SPH' => 'INTERNAL SPH',
                    'INTERNAL OTHERS' => 'INTERNAL OTHERS',
                ],
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'required' => true,
                'placeholder' => 'spq.form.make_selection',
                'label' => 'spq.quotations.fields.currency',
                'translation_domain' => 'spq',
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'fields.amount',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }
}
