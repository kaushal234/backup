<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallNestedCustomerServiceRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nestedTechnicianOnCallCustomerServiceRecord', NestedCustomerServiceRecordType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'toc.button.create_csr',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'technician_on_call',
        ]);
    }
}
