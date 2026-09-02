<?php

declare(strict_types=1);

namespace App\Form\VendorWarrantyClaim;

use App\DataTransferObject\VendorWarrantyClaim\EditVendorWarrantyClaim;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplierCreditAmount', NumberType::class, [
                'required' => true,
            ])
            ->add('supplierShippingInstruction', TextareaType::class, [
                'required' => true,
            ])
            ->add('accepted', CheckboxType::class, [
                'required' => false,
            ])
            ->add('shipBackDefectivePart', CheckboxType::class, [
                'required' => false,
            ])
            ->add('supplierReturnMerchandiseAuthorization', TextareaType::class, [
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EditVendorWarrantyClaim::class,
            'csrf_protection' => true,
        ]);
    }
}
