<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaimThreshold;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimThresholdsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('thresholdForms', CollectionType::class, [
                'entry_type' => VendorWarrantyClaimThresholdType::class,
                'data' => $builder->getData(),
                'label' => false,
                'entry_options' => [
                    'label' => false,
                    'action_type' => $options['action_type'],
                ],
                'allow_add' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'vendor_warranty_claim',
            'action_type' => 'edit',
        ]);
    }
}
