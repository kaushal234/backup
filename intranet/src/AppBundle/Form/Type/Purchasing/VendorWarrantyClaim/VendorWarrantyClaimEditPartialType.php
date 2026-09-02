<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class VendorWarrantyClaimEditPartialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplierReturnMerchandiseAuthorization', TextareaType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_rma',
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
                'required' => false,
            ])
            ->add('supplierShippingInstruction', TextareaType::class, [
                'label' => 'vendor_warranty_claim.fields.shipping_instructions',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
            ])
            ->add('trackingNumber', TextType::class, [
                'label' => 'tracking.fields.tracking_number',
                'translation_domain' => 'tracking',
                'required' => false,
            ])
            ->add('supplierCreditNote', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_credit_note',
                'required' => false,
            ])
            ->add('supplierCreditAmount', NumberType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_credit_amount',
                'constraints' => [new Range(['min' => 0])],
                'required' => false,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'label' => 'directory.location.fields.currency',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        foreach (array_keys($builder->all()) as $key) {
            if ('submit' === $key) {
                continue;
            }
            if (!\in_array($key, $options['authorized_fields'], true)) {
                $field = $builder->get($key);
                $field->setDisabled(true);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'vendor_warranty_claim',
            'authorized_fields' => [],
        ]);
    }
}
