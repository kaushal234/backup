<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentShippingRecordLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'required' => true,
            ])
            ->add('estimatedPickUpDate', DatePickerType::class, [
                'required' => false,
                'label' => 'equipment_shipping_record.fields.line.estimated_pick_up_date',
            ])
            ->add('vesselLoadingDate', DatePickerType::class, [
                'required' => false,
                'label' => 'equipment_shipping_record.fields.line.vessel_loading_date',
            ])
            ->add('estimatedArrivalDate', DatePickerType::class, [
                'required' => false,
                'label' => 'equipment_shipping_record.fields.line.estimated_arrival_date',
            ])
            ->add('actualArrivalDate', DatePickerType::class, [
                'required' => false,
                'label' => 'equipment_shipping_record.fields.line.actual_arrival_date',
            ])
            ->add('truckType', TextType::class, [
                'required' => false,
                'label' => 'equipment_shipping_record.fields.line.truck_type',
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'label' => 'demo.fields.comment',
                'translation_domain' => 'demo',
            ])
            ->add('estimatedPickUpDateConfirmation', CheckboxType::class, [
                'required' => false,
                'label' => 'equipment_shipping_record.fields.line.estimated_pick_up_date_confirmation',
                'disabled' => !$options['can_edit_pickup_confirmation'],
                'help' => !$options['can_edit_pickup_confirmation']
                    ? '<span class="text-warning">⚠ PSM team only</span>'
                    : null,
                'help_html' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
            'can_edit_pickup_confirmation' => false,
        ]);
    }
}
