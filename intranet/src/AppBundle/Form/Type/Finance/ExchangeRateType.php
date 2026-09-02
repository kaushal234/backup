<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use AppBundle\Form\Type\Common\MonthPickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeRateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'forex.fields.rate_type',
                'choice_translation_domain' => false,
                'placeholder' => 'forex.make_selection',
                'choices' => [
                    'AVG' => 'AVG',
                    'END' => 'END',
                    'TLD' => 'TLD',
                ],
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'choice_translation_domain' => false,
            ])
            ->add('applicatedOn', MonthPickerType::class, [
                'label' => 'forex.fields.applicated_on',
                'required' => false,
            ])
            ->add('rate', TextType::class, [
                'label' => 'forex.fields.rate_against_euro',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forex',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
