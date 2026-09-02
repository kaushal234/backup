<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\CompetitorPricing;

use AppBundle\Form\Type\BasicFileType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorChoiceType;
use AppBundle\Form\Type\Sales\IncotermChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitorPricingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quotationDate', DatePickerType::class, [
                'widget' => 'single_text',
                'label' => 'competitor_pricings.fields.quotation_date',
            ])
            ->add('competitor', CompetitorChoiceType::class, [
                'label' => 'competitors.name',
                'placeholder' => '',
                'translation_domain' => 'sales_competitors',
            ])
            ->add('competitorModel', TextType::class, [
                'label' => 'competitor_pricings.fields.model',
            ])
            ->add('competitorOptions', TextareaType::class, [
                'required' => false,
                'label' => 'competitor_pricings.fields.options',
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'forecast_closures.fields.quantity',
                'translation_domain' => 'forecast_closures',
            ])
            ->add('price', NumberType::class, [
                'label' => 'competitor_pricings.fields.price_full',
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'label' => 'competitor_pricings.fields.currency',
                'placeholder' => '',
            ])
            ->add('exchangeRate', NumberType::class, [
                'label' => 'competitor_pricings.fields.exchange_rate',
            ])
            ->add('markupPercentage', PercentType::class, [
                'required' => false,
                'type' => 'integer',
                'label' => 'competitor_pricings.fields.markup',
            ])
            ->add('incoterm', IncotermChoiceType::class, [
                'label' => 'competitor_pricings.fields.incoterms',
                'placeholder' => '',
            ])
            ->add('incotermsLocation', TextType::class, [
                'required' => false,
                'label' => 'competitor_pricings.fields.incoterms_location',
            ])
            ->add('file', BasicFileType::class, [
                'label' => 'menu.file',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'translation_domain' => $options['submit_translation_domain'],
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'competitor_pricings',
            'submit_label' => 'button.submit',
            'submit_translation_domain' => 'messages',
            'allow_extra_fields' => true,
        ]);
    }
}
