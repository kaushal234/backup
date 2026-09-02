<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use AppBundle\Form\Type\Finance\CreditLimitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerCreditLimitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('creditLimits', CollectionType::class, [
                'entry_type' => CreditLimitType::class,
                'label' => 'customers.fields.credit_limit',
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
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
            'translation_domain' => 'sales_customers',
        ]);
    }
}
