<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Purchasing\BusinessPartner\IONBusinessPartnerAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\SupplierCorrectiveActionRequest\SupplierCorrectiveActionRequestChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class VendorWarrantyClaimEditType extends AbstractType
{
    private readonly string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $vendorWarrantyClaim = $builder->getData();
        $parts = array_reduce($vendorWarrantyClaim['parts'], static function ($memo, $part) {
            $memo[] = $part['partNumber'];

            return $memo;
        }, []);
        $builder
            ->add('scarRequested', CheckboxType::class, [
                'label' => 'vendor_warranty_claim.fields.scar_requested',
                'required' => false,
                'attr' => ['class' => 'disable-field'],
            ])
            ->add('supplierCorrectiveActionRequest', SupplierCorrectiveActionRequestChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.scar',
                'parts' => $parts,
                'required' => false,
                'attr' => ['class' => 'field-to-disable'],
                'help' => 'vendor_warranty_claim.title.scar_help',
            ])
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.department.fields.location',
                'translation_domain' => 'directory',
                'erp_in_label' => true,
                'filters' => ['has_any_capability' => ['factory', 'sso', 'sparePartsHub']],
            ])
            ->add('assignee', PeopleAutocompleteChoiceType::class, [
                'label' => 'tasks.assignee',
                'translation_domain' => 'messages',
                'required' => false,
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
            ->add('supplierShipperName', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_shipper_name',
                'required' => false,
            ])
            ->add('requestedCreditAmount', NumberType::class, [
                'label' => 'vendor_warranty_claim.fields.requested_credit_amount',
                'constraints' => [new Range(['min' => 0])],
                'required' => false,
            ])
            ->add('actualCreditAmount', NumberType::class, [
                'label' => 'vendor_warranty_claim.fields.actual_credit_amount',
                'constraints' => [new Range(['min' => 0])],
                'required' => false,
            ])
            ->add('costBreakdown', TextareaType::class, [
                'label' => 'vendor_warranty_claim.fields.cost_breakdown',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
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
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if (!isset($vendorWarrantyClaim['nonConformity'])) {
            $file = isset($vendorWarrantyClaim['mainFile']) && null !== $vendorWarrantyClaim['mainFile'] && file_exists($filePath = \sprintf('%s/%s', $this->uploadDir, $vendorWarrantyClaim['mainFile']['filePath'])) ? new File($filePath) : null;
            $builder->add('mainFile', FileType::class, [
                'data' => $file,
                'translation_domain' => 'file_type',
                'label' => 'file_type.file_upload',
                'required' => false,
                'help' => $vendorWarrantyClaim['mainFile']['filePath'] ?? null,
            ]);
        }

        foreach (array_keys($builder->all()) as $key) {
            if ('submit' === $key) {
                continue;
            }
            if (!\in_array($key, $options['authorized_fields'], true)) {
                if ('mainFile' === $key) {
                    continue;
                }
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
