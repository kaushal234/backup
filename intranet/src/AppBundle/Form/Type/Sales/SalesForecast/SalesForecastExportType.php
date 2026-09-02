<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesForecast;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAt', DatePickerType::class, [
                'label' => 'sales_forecasts.fields.created_after',
                'property_path' => '[createdAt][after]',
            ])
            ->add('product', ProductChoiceType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'required' => false,
                'multiple' => true,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('customer', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'sales_forecasts.fields.customer',
                'required' => false,
                'property_path' => '[customer_with_children]',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.status',
                'required' => false,
                'choices' => [
                    'BUDGET' => 'BUDGET',
                    'DELAYED' => 'DELAYED',
                    'IN PROGRESS' => 'IN_PROGRESS',
                    'ORDERED' => 'ORDERED',
                    'LOST' => 'LOST',
                    'CANCELLED' => 'CANCELLED',
                    'PARTIAL' => 'PARTIAL',
                ],
                'data' => ['BUDGET', 'DELAYED', 'IN_PROGRESS'],
                'multiple' => true,
            ])
            ->add('asm', ASMAutocompleteChoiceType::class, [
                'label' => 'sales_forecasts.fields.asm',
                'required' => false,
                'multiple' => true,
            ])
            ->add('format', ChoiceType::class, [
                'label' => 'fields.file_format',
                'translation_domain' => 'messages',
                'choices' => [
                    'csv' => 'csv',
                    'xlsx' => 'xlsx',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
        ]);
    }
}
