<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Common\DateRangePickerType;
use AppBundle\Form\Type\Directory\Location\LocationAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class VendorWarrantyClaimFlopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationAutocompleteChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.factory',
                'translation_domain' => 'vendor_warranty_claim',
                'required' => false,
            ])
            ->add('createdAt', DateRangePickerType::class, [
                'label' => 'vendor_warranty_claim.fields.created_at',
                'translation_domain' => 'vendor_warranty_claim',
                'required' => false,
            ])
            ->add('status', VendorWarrantyClaimStatusChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }
}
