<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\People\BuyerChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\QualityPeopleAutocompleteChoiceType;
use AppBundle\Form\Type\ImportanceFactorType;
use AppBundle\Form\Type\Purchasing\BusinessPartner\IONBusinessPartnerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class FirstArticleQualificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', FactoryChoiceType::class, [
                'required' => true,
                'text_key' => null,
                'template' => '{{ erp }} {{ name }}',
            ])
            ->add('buyer', BuyerChoiceType::class, [
                'label' => 'fields.buyer',
                'required' => false,
            ])
            ->add('owner', QualityPeopleAutocompleteChoiceType::class, [
                'label' => 'fields.owner',
                'required' => true,
            ])
            ->add('supplier', IONBusinessPartnerAutocompleteChoiceType::class, [
                'label' => 'finance.approver.supplier',
                'translation_domain' => 'finance',
                'required' => false,
                'property_path' => '[supplierNumber]',
            ])
            ->add('iFactor', ImportanceFactorType::class, [
                'required' => false,
            ])
            ->add('planDefinitionDueDate', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.plan_definition_due_date.date',
                'translation_domain' => 'first_article_qualification',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'restrictions' => [
                    'minDateStr' => '+0 day',
                ],
            ])
            ->add('deliverablesDueDate', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.deliverables_due_date.date',
                'translation_domain' => 'first_article_qualification',
                'required' => false,
            ])
            ->add('dueDate', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.faq_due_date',
                'translation_domain' => 'first_article_qualification',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'restrictions' => [
                    'minField' => 'planDefinitionDueDate',
                ],
            ])
            ->add('eap', IntegerType::class, [
                'label' => 'EAP',
                'required' => false,
            ])
            ->add('meap', IntegerType::class, [
                'label' => 'MEAP',
                'required' => false,
            ])
            ->add('tags', FirstArticleQualificationTagChoiceType::class, [
                'label' => 'fields.tags',
                'required' => false,
                'multiple' => true,
            ])
            ->add('members', PeopleAutocompleteChoiceType::class, [
                'label' => 'fields.members',
                'multiple' => true,
                'required' => false,
            ])
            ->add('partNumbers', CollectionType::class, [
                'entry_type' => PartNumberLineType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => false,
            ])
            ->add('equipmentRecords', EquipmentRecordAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('productFamily', ProductFamilyChoiceType::class, [
                'label' => 'catalogue.family.product_family',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['partNumbers']) && \is_array($data['partNumbers'])) {
                foreach ($data['partNumbers'] as $key => $line) {
                    $hasPartNumber = !empty($line['number']);
                    $hasRevision = !empty($line['revision']);
                    $hasDesc = !empty($line['description']);

                    if (!$hasPartNumber && !$hasRevision && !$hasDesc) {
                        unset($data['partNumbers'][$key]);
                    }
                }

                $data['partNumbers'] = array_values($data['partNumbers']);
                $event->setData($data);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) {
            $originalData = $event->getData();

            if ($originalData instanceof ApiData) {
                $data = $originalData->toArray();
                $isApiData = true;
            } elseif (\is_array($originalData)) {
                $data = $originalData;
                $isApiData = false;
            } else {
                return;
            }

            $erp = $data['erp'] ?? $data['location']['erp'] ?? null;

            if (null === $erp || !isset($data['partNumbers']) || !\is_array($data['partNumbers'])) {
                return;
            }

            $changed = false;

            foreach ($data['partNumbers'] as $key => $line) {
                if (!empty($line['number']) && !str_contains($line['number'], '/ion/items/')) {
                    $data['partNumbers'][$key]['number'] = \sprintf('/ion/items/site=%s;item=%s', $erp, $line['number']);
                    $changed = true;
                }
            }

            if ($changed) {
                $event->setData($isApiData ? new ApiData($data) : $data);
            }
        });
    }
}
