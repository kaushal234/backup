<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentShippingRecordCostType extends AbstractType
{
    public const TRANSPORT = 'Transport';
    public const LOADING_UNLOADING = 'Loading / Unloading';
    public const CUSTOMS_DUTIES = 'Customs duties';
    public const OTHER = 'Others';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('costDate', DatePickerType::class, [
                'required' => true,
                'label' => 'equipment_shipping_record.fields.cost.cost_date',
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'label' => 'equipment_shipping_record.fields.cost.cost_type',
                'choices' => $this::getTypeOptions(),
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'equipment_shipping_record.fields.cost.description',
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'required' => true,
                'placeholder' => 'spq.form.make_selection',
                'label' => 'spq.quotations.fields.currency',
                'translation_domain' => 'spq',
            ])
            ->add('price', NumberType::class, [
                'scale' => 2,
                'required' => true,
                'label' => 'equipment_shipping_record.fields.price',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-info',
                    'style' => 'text-transform: uppercase;',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
        ]);
    }

    public static function getTypeOptions(): array
    {
        return [
            self::TRANSPORT => self::TRANSPORT,
            self::LOADING_UNLOADING => self::LOADING_UNLOADING,
            self::CUSTOMS_DUTIES => self::CUSTOMS_DUTIES,
            self::OTHER => self::OTHER,
        ];
    }
}
