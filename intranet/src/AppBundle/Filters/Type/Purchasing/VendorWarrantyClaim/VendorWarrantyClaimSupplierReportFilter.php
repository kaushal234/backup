<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimSupplierReportFilter extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationChoiceType::class, [
                'label' => 'fields.location',
                'required' => false,
            ])
            ->add('supplierNumber', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_number',
                'required' => false,
                'translation_domain' => 'vendor_warranty_claim',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('rejected', CheckboxType::class, [
                'label' => 'vendor_warranty_claim.title.rejected',
                'required' => false,
                'translation_domain' => 'vendor_warranty_claim',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
        ]);
    }
}
