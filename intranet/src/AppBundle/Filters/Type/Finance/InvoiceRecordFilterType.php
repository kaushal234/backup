<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Manager\Finance\InvoiceRecord\InvoiceRecordCategories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceRecordFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerErpReference_customer', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'required' => false,
                'property_path' => '[customerErpReference.customer]',
            ])
            ->add('customerErpReference_sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'property_path' => '[customerErpReference.sso]',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'account_receivable.invoice_record.fields.category',
                'translation_domain' => 'account_receivable',
                'choice_translation_domain' => false,
                'choices' => InvoiceRecordCategories::getCategories(),
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
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
