<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Form type for adding new Calibration_tool.
 */
class ToolType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serialNumber', TextType::class, [
                'label' => 'calibration_tool.fields.serialNumber',
                'constraints' => [
                    new NotBlank(message: 'not_blank'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'calibration_tool.fields.description',
                'required' => false,
            ])
            ->add('calibrationInterval', IntegerType::class, [
                'label' => 'calibration_tool.fields.calibrationInterval',
                'constraints' => [
                    new Range(invalidMessage: 'invalid_number_of_days', min: 1, max: 99_999_999_999),
                ],
            ])
            ->add('calibrationIntervalUnit', ChoiceType::class, [
                'choices' => [
                    'calibration_tool.fields.calibrationIntervalUnit.months' => 'months',
                    'calibration_tool.fields.calibrationIntervalUnit.weeks' => 'weeks',
                    'calibration_tool.fields.calibrationIntervalUnit.days' => 'days',
                ],
                'data' => 'days',
                'mapped' => false,
            ])
            ->add('calibrationNotice', IntegerType::class, [
                'label' => 'calibration_tool.fields.calibrationNotice',
                'attr' => [
                    'input-group-text' => 'days',
                ],
                'constraints' => [
                    new Range(invalidMessage: 'calibration_tool.validation_errors.invalid_message', min: 1, max: 99_999_999_999),
                ],
            ])
            ->add('nextCalibrationDate', DatePickerType::class, [
                'label' => 'calibration_tool.fields.nextCalibrationDate',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('purchasingDate', DatePickerType::class, [
                'label' => 'calibration_tool.fields.purchasingDate',
                'required' => true,
                'data' => $options['data']['purchasingDate'] ?? date(DatePickerType::DEFAULT_INPUT_FORMAT),
            ])
            ->add('vendorId', TextType::class, [
                'label' => 'calibration_tool.fields.vendorId',
                'required' => false,
            ])
            ->add('vendorErp', TextType::class, [
                'label' => 'calibration_tool.fields.vendorErp',
                'required' => false,
            ])
            ->add('vendorName', TextType::class, [
                'label' => 'calibration_tool.fields.vendorName',
                'required' => false,
            ])
            ->add('toolType', ToolTypeChoiceType::class, [
                'label' => 'calibration_tool.fields.tool_type',
            ])
            ->add('locationArea', LocationAreaChoiceType::class, [
                'label' => 'calibration_tool.fields.location_area',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'calibration_tool',
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_quality_calibratedtools_tool';
    }
}
