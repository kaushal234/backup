<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\JuridicalLocation\JuridicalLocationChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('juridicalLocation', JuridicalLocationChoiceType::class, [
                'label' => 'directory.juridical_location.name',
                'translation_domain' => 'directory',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('baanCustomerNumber', TextType::class, [
                'label' => 'spq.quotations.fields.cuno',
                'translation_domain' => 'spq',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'fields.status',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'required' => false,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'IN PROGRESS' => 'iN PROGRESS',
                    'CLOSED' => 'CLOSED',
                ],
            ])
            ->add('equoteId', TextType::class, [
                'label' => 'sales_forecasts.fields.equote',
                'translation_domain' => 'sales_forecasts',
                'required' => false,
            ])
            ->add('baanOrderNumbers', TextType::class, [
                'label' => 'sales_order.fields.baan_order_number',
                'translation_domain' => 'sales_orders',
                'required' => false,
            ])
            ->add('asm', ASMChoiceType::class, [
                'label' => 'customers.fields.asm',
                'translation_domain' => 'sales_customers',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('buyer', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('endUser', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('newCustomer', ChoiceType::class, [
                'label' => 'sales_order.fields.new_customer',
                'choice_translation_domain' => false,
                'choices' => ['Yes' => 1, 'No' => 0],
                'required' => false,
            ])
            ->add('salesAgent', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'sales_order.fields.sales_agent',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('customerPurchaseOrders', TextType::class, [
                'label' => 'sales_order.fields.customer_purchase_orders',
                'required' => false,
            ])
            ->add('entered_at_after', DatePickerType::class, [
                'label' => 'sales_order.fields.entered_at_after',
                'property_path' => '[enteredAt][after]',
                'required' => false,
            ])
            ->add('entered_at_before', DatePickerType::class, [
                'label' => 'sales_order.fields.entered_at_before',
                'property_path' => '[enteredAt][before]',
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
            'translation_domain' => 'sales_orders',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_sales_orders_filters';
    }
}
