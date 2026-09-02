<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Common\SwitchType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use AppBundle\Form\Type\Directory\Region\RegionChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerTypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asm', ASMChoiceType::class, [
                'property_path' => '[mainSalesRepresentative.asm]',
                'label' => 'customers.fields.main_asm',
                'required' => false,
            ])
            ->add('customerTypes', CustomerTypeChoiceType::class, [
                'label' => 'customers.fields.type',
                'required' => false,
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'customers.fields.address_country',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'customers.fields.status',
                'required' => false,
                'multiple' => true,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'APPROVED' => 'APPROVED',
                    'NOT APPROVED' => 'NOT APPROVED',
                    'NOT ACTIVE' => 'NOT ACTIVE',
                    'PENDING RE-APPROVAL' => 'PENDING RE-APPROVAL',
                ],
            ])
            ->add('main_representative_exists', SwitchType::class, [
                'label' => 'customers.fields.no_asm',
                'required' => false,
            ])
            ->add('has_crt', SwitchType::class, [
                'label' => 'customers.fields.no_crt',
                'required' => false,
            ])
            ->add('watchList', SwitchType::class, [
                'label' => 'customers.fields.watch_list',
                'required' => false,
            ])
            ->add('region', RegionChoiceType::class, [
                'label' => 'directory.people.fields.region',
                'translation_domain' => 'directory',
                'property_path' => '[mainSalesRepresentative.asm.businessUnit.region]',
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
            'translation_domain' => 'sales_customers',
            'csrf_protection' => false,
        ]);
    }

    public function getName(): string
    {
        return 'app_sales_customer_filters';
    }
}
