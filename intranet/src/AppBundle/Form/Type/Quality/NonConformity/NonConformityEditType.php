<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\NonConformity;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use AppBundle\Form\Type\ImportanceFactorType;
use AppBundle\Form\Type\Purchasing\BusinessPartner\IONBusinessPartnerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NonConformityEditType extends AbstractType
{
    private readonly string $uploadDir;
    private readonly Security $security;

    public function __construct(Security $security, string $uploadDir)
    {
        $this->security = $security;
        $this->uploadDir = $uploadDir;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $nonConformity = $builder->getData();
        $file = isset($nonConformity['mainFile']) && null !== $nonConformity['mainFile'] && file_exists($filePath = \sprintf('%s/%s', $this->uploadDir, $nonConformity['mainFile']['filePath'])) ? new File($filePath) : null;
        $builder
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.department.fields.location',
                'translation_domain' => 'directory',
                'extra_choices' => ['' => ''],
                'erp_in_label' => true,
                'filters' => ['has_any_capability' => ['factory', 'sso', 'sparePartsHub']],
                'constraints' => [new NotBlank()],
            ])
            ->add('reportedBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'non_conformity.fields.reported_by',
                'translation_domain' => 'non_conformity',
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'mis.modules.fields.short_desc',
                'translation_domain' => 'mis',
                'constraints' => [new NotBlank()],
            ])
            ->add('problem', TextareaType::class, [
                'label' => 'non_conformity.fields.problem',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('failureType', ChoiceType::class, [
                'label' => 'non_conformity.fields.failure_type',
                'choice_translation_domain' => false,
                'required' => false,
                'choices' => [
                    'Hydraulic' => 'Hydraulic',
                    'Electrical' => 'Electrical',
                    'Mechanical' => 'Mechanical',
                    'Weldment' => 'Weldment',
                    'Paint' => 'Paint',
                    'Surface coating' => 'Surface coating',
                    'Engine & Power Train System' => 'Engine & power train system',
                    'Administration' => 'Administration',
                ],
            ])
            ->add('iFactor', ImportanceFactorType::class, [
                'label' => 'fields.ifactor',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('mainFile', FileType::class, [
                'data' => $file,
                'translation_domain' => 'file_type',
                'label' => 'file_type.file_upload',
                'required' => false,
                'help' => $nonConformity['mainFile']['filePath'] ?? null,
            ])
            ->add('processes', ProcessChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('investigation', TextareaType::class, [
                'label' => 'non_conformity.fields.investigation',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
                'required' => false,
            ])
            ->add('responsibles', ResponsibleChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('environmentalIssue', CheckboxType::class, [
                'label' => 'non_conformity.fields.environmental_issue',
                'attr' => ['class' => 'disable-field'],
                'required' => false,
            ])
            ->add('safety', CheckboxType::class, [
                'label' => 'non_conformity.fields.safety',
                'attr' => ['class' => 'disable-field'],
                'required' => false,
            ])
            ->add('purchaseOrderNumber', TextType::class, [
                'label' => 'spq.quotations.fields.customer.purchase_order',
                'translation_domain' => 'spq',
                'required' => false,
            ])
            ->add('rush', CheckboxType::class, [
                'label' => 'non_conformity.fields.rush',
                'required' => false,
            ])
            ->add('scrap', CheckboxType::class, [
                'label' => 'non_conformity.fields.scrap',
                'required' => false,
            ])
            ->add('rework', CheckboxType::class, [
                'label' => 'non_conformity.fields.rework',
                'required' => false,
            ])
            ->add('firstArticleInspection', CheckboxType::class, [
                'label' => 'non_conformity.fields.fai',
                'required' => false,
            ])
            ->add('useAsIs', CheckboxType::class, [
                'label' => 'non_conformity.fields.use_as_is',
                'required' => false,
            ])
            ->add('returnVendor', CheckboxType::class, [
                'label' => 'non_conformity.fields.return_vendor',
                'required' => false,
            ])
            ->add('chargeVendorForRepair', CheckboxType::class, [
                'label' => 'non_conformity.fields.charge_vendor',
                'required' => false,
            ])
            ->add('supplierCorrectiveActionRequest', CheckboxType::class, [
                'label' => 'non_conformity.fields.scar',
                'required' => false,
            ])
            ->add('internalCorrectiveActionRequest', CheckboxType::class, [
                'label' => 'non_conformity.fields.car',
                'required' => false,
            ])
            ->add('containment', CheckboxType::class, [
                'label' => 'non_conformity.fields.containment',
                'required' => false,
            ])
            ->add('other', CheckboxType::class, [
                'label' => 'non_conformity.fields.other',
                'required' => false,
            ])
            ->add('actionComment', TextareaType::class, [
                'label' => 'non_conformity.fields.action_comment',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('repairApprover', PeopleAutocompleteChoiceType::class, [
                'label' => 'non_conformity.fields.repair_approver',
                'required' => false,
            ])
            ->add('repairApprovalDate', DatePickerType::class, [
                'required' => false,
                'label' => 'non_conformity.fields.repair_approval_date',
            ])
            ->add('solution', TextareaType::class, [
                'label' => 'non_conformity.fields.solution',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('supplierNumber', IONBusinessPartnerAutocompleteChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_number',
                'translation_domain' => 'vendor_warranty_claim',
            ])
            ->add('hours', IntegerType::class, [
                'label' => 'non_conformity.fields.hours',
                'required' => false,
            ])
            ->add('cost', NumberType::class, [
                'label' => 'non_conformity.fields.costs',
                'required' => false,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'label' => 'spq.quotations.fields.currency',
                'translation_domain' => 'spq',
                'required' => false,
                'placeholder' => 'spq.form.make_selection',
            ])
            ->add('costBreakdown', TextareaType::class, [
                'label' => 'non_conformity.fields.cost_breakdown',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('nonQualityCost', NumberType::class, [
                'label' => 'non_conformity.fields.non_quality_cost',
                'required' => false,
            ])
            ->add('workOrderReference', TextType::class, [
                'label' => 'non_conformity.fields.work_order_reference',
                'required' => false,
            ])
            ->add('invoiceNumber', TextType::class, [
                'label' => 'account_receivable.invoice_record.fields.invoice_number',
                'translation_domain' => 'account_receivable',
                'required' => false,
            ])
            ->add('equipmentRecords', EquipmentRecordAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
            ])
            ->add('products', ProductAutocompleteChoiceType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        foreach (array_keys($builder->all()) as $key) {
            if (\in_array($key, ['submit', 'mainFile'], true)) {
                continue;
            }
            if (!\in_array($key, $options['authorized_fields'], true)) {
                $field = $builder->get($key);
                $field->setDisabled(true);
            }
        }

        if (!$this->security->isGranted('FEATURE_NON_CONFORMITY_EDIT')) {
            $builder->get('mainFile')->setDisabled(true);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'non_conformity',
            'authorized_fields' => [],
        ]);
    }
}
