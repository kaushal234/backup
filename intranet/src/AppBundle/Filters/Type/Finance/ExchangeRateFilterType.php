<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use AppBundle\Form\Type\Common\MonthPickerType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeRateFilterType extends AbstractType
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
                'required' => false,
                'choices' => [
                    'AVG' => 'AVG',
                    'END' => 'END',
                    'TLD' => 'TLD',
                ],
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('applicated_on_after', MonthPickerType::class, [
                'label' => 'forex.fields.applicated_on_after',
                'property_path' => '[applicatedOn][after]',
                'required' => false,
            ])
            ->add('applicated_on_before', MonthPickerType::class, [
                'label' => 'forex.fields.applicated_on_before',
                'property_path' => '[applicatedOn][before]',
                'required' => false,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
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
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
