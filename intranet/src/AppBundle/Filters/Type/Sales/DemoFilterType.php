<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Common\SwitchType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerTypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemoFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asm', ASMChoiceType::class, [
                'label' => 'demo.fields.asm',
                'required' => false,
            ])
            ->add('customer', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'required' => false,
            ])
            ->add('customer_type', CustomerTypeChoiceType::class, [
                'label' => 'customers.fields.type',
                'required' => false,
                'translation_domain' => 'sales_customers',
                'property_path' => '[customer.customerTypes]',
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'customers.fields.address_country',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('product', ProductAutocompleteChoiceType::class, [
                'label' => 'demo.fields.product',
                'translation_domain' => 'demo',
                'required' => false,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'required' => false,
                'translation_domain' => 'messages',
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'required' => false,
                'translation_domain' => 'messages',
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'demo.fields.status',
                'choice_translation_domain' => false,
                'multiple' => true,
                'required' => false,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'APPROVED' => 'APPROVED',
                    'SUBMITTED' => 'SUBMITTED',
                    'ACTIVE' => 'ACTIVE',
                    'REJECTED' => 'REJECTED',
                    'SUCCESSFUL' => 'SUCCESSFUL',
                    'SUCCESSFUL FUTURE SALE' => 'SUCCESSFUL_FUTURE_SALE',
                    'UNSUCCESSFUL' => 'UNSUCCESSFUL',
                ],
            ])
            ->add('delinquent', SwitchType::class, [
                'label' => 'demo.fields.delinquent',
                'required' => false,
            ])
            ->add('open', SwitchType::class, [
                'label' => 'demo.fields.open',
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
            'translation_domain' => 'demo',
            'csrf_protection' => false,
        ]);
    }
}
