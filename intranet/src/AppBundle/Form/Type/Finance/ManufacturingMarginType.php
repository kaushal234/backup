<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManufacturingMarginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $manufacturingMargin = $builder->getData();
        $builder
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'label' => 'demo.fields.equipment_record',
                'translation_domain' => 'demo',
                'required' => true,
            ])
            ->add('exportedAt', DatePickerType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month',
                ],
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => 'now',
                ],
                'required' => true,
                'data' => $manufacturingMargin['exportedAt'] ?? date(DatePickerType::DEFAULT_INPUT_FORMAT),
                'translation_domain' => 'messages',
                'label' => 'fields.date',
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'required' => true,
                'choice_translation_domain' => false,
            ])
            ->add('factoryRevenue', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.factory_revenue',
            ])
            ->add('standardHours', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.standard_hours',
            ])
            ->add('actualHours', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.actual_hours',
            ])
            ->add('optionConfigurationParameterHours', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.ocp_hours',
            ])
            ->add('standardLabourCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.standard_labour_cost',
            ])
            ->add('actualLabourCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.actual_labour_cost',
            ])
            ->add('standardMaterialCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.standard_material_cost',
            ])
            ->add('actualMaterialCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.actual_material_cost',
            ])
            ->add('standardOtherMaterialCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.standard_other_material_cost',
            ])
            ->add('actualOtherMaterialCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.actual_other_material_cost',
            ])
            ->add('standardOtherDirectCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.standard_other_direct_cost',
            ])
            ->add('actualOtherDirectCost', NumberType::class, [
                'required' => true,
                'label' => 'manufacturing_margin.field.actual_other_direct_cost',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'fields.comment',
                'translation_domain' => 'messages',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'manufacturing_margin',
        ]);
    }
}
