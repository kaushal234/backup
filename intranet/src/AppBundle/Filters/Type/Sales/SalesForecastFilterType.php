<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use AppBundle\Form\Type\Directory\People\BuyerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asm', ASMChoiceType::class, [
                'label' => 'sales_forecasts.fields.asm',
                'required' => false,
            ])
            ->add('buyer', BuyerAutocompleteChoiceType::class, [
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('endUser', CustomerAutocompleteChoiceType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.status',
                'choice_translation_domain' => false,
                'required' => false,
                'choices' => [
                    'BUDGET' => 'BUDGET',
                    'IN_PROGRESS' => 'IN_PROGRESS',
                    'DELAYED' => 'DELAYED',
                    'ORDERED' => 'ORDERED',
                    'LOST' => 'LOST',
                    'CANCELLED' => 'CANCELLED',
                    'PARTIAL' => 'PARTIAL',
                ],
            ])
            ->add('closedAt-after', DatePickerType::class, [
                'label' => 'sales_forecasts.fields.closed_after',
                'required' => false,
            ])
            ->add('createdAt-after', DatePickerType::class, [
                'label' => 'sales_forecasts.fields.created_after',
                'required' => false,
            ])
            ->add('delinquent', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.delinquent',
                'required' => false,
            ])
            ->add('airport', AirportChoiceType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('hot_deals', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.hot_deals',
                'required' => false,
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'address.fields.country',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('family', ProductFamilyChoiceType::class, [
                'label' => 'catalogue.family.product_family',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
            ->add('tier', EmissionRatingChoiceType::class, [
                'label' => 'sales_forecasts.fields.tier',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
