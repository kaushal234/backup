<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use AppBundle\Form\Type\Purchasing\BusinessPartner\IONBusinessPartnerAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\SupplierCorrectiveActionRequest\SupplierCorrectiveActionRequestChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class VendorWarrantyClaimAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $vendorWarrantyClaim = $builder->getData();

        $parts = array_column($vendorWarrantyClaim['parts'], 'partNumber');
        $builder
            ->add('supplier', ChoiceType::class, [
                'label' => 'finance.approver.supplier',
                'translation_domain' => 'finance',
                'choice_translation_domain' => false,
                'required' => false,
                'choices' => $options['suppliers'],
                'attr' => ['class' => 'supplier'],
            ])
            ->add('scarRequested', CheckboxType::class, [
                'label' => 'vendor_warranty_claim.fields.scar_requested',
                'attr' => ['class' => 'disable-field'],
                'required' => false,
            ])
            ->add('supplierCorrectiveActionRequest', SupplierCorrectiveActionRequestChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.scar',
                'attr' => ['class' => 'field-to-disable'],
                'parts' => $parts,
                'required' => false,
            ])
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.department.fields.location',
                'translation_domain' => 'directory',
                'erp_in_label' => true,
                'filters' => ['has_any_capability' => ['factory', 'sso', 'sparePartsHub']],
            ])
            ->add('type', VendorWarrantyClaimTypeChoiceType::class, [
                'label' => 'spare_parts_request.fields.type',
                'translation_domain' => 'spare_parts_request',
            ])
            ->add('supplierNumber', IONBusinessPartnerAutocompleteChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_number',
                'translation_domain' => 'vendor_warranty_claim',
            ])
            ->add('requestedSupplierAction', TextareaType::class, [
                'label' => 'vendor_warranty_claim.fields.requested_supplier_action',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'label' => 'directory.location.fields.currency',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('supplierStockVerified', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_stock_verified',
                'required' => false,
            ])
            ->add('tldStockVerified', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.tld_stock_verified',
                'required' => false,
            ])
            ->add('issueOrigin', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.issue_origin',
                'required' => false,
            ])
            ->add('correctiveAction', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.corrective_action',
                'required' => false,
            ])
            ->add('requestedCreditAmount', NumberType::class, [
                'label' => 'vendor_warranty_claim.fields.requested_credit_amount',
                'constraints' => [new Range(['min' => 0])],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if ($options['addPhoto']) {
            $builder->add('mainFile', FileType::class, [
                'translation_domain' => 'file_type',
                'label' => 'file_type.file_upload',
                'required' => false,
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ('' === $data['supplierNumber'] && '' === $data['supplier']) {
                $form->addError(new FormError('Supplier is mandatory'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'vendor_warranty_claim',
            'suppliers' => [],
            'addPhoto' => false,
        ]);
    }
}
