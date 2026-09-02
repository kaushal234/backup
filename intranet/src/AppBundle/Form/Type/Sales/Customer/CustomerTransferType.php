<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerTransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'customers.fields.name',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_customers',
        ]);
    }

    public function getName(): string
    {
        return 'app_sales_customer_transfer';
    }
}
