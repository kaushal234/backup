<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToolCertificateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('certificationFile', FileType::class, [
                'label' => 'calibration_tool.fields.certificationFile',
                'required' => true,
            ])
            ->add('calibrationDate', DatePickerType::class, [
                'label' => 'calibration_tool.fields.calibrationDate',
                'required' => true,
            ])
            ->add('nextCalibrationDate', DatePickerType::class, [
                'label' => 'calibration_tool.fields.nextCalibrationDate',
                'required' => true,
            ])
            ->add('otf', CheckboxType::class, [
                'label' => 'calibration_tool.new_otf',
                'required' => false,
                'data' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'calibration_tool',
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_bundle_tool_certificate_form_type';
    }
}
