<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance;

use AppBundle\Form\Type\Common\MonthPickerType;
use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManufacturingReportDownloadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from', MonthPickerType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month',
                ],
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => 'now',
                ],
                'label' => 'fields.from',
            ])
            ->add('to', MonthPickerType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month',
                ],
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => 'now',
                ],
                'label' => 'fields.to',
            ])
            ->add('full', CheckboxType::class, [
                'required' => false,
                'translation_domain' => 'manufacturing_margin',
                'label' => 'manufacturing_margin.field.full',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if (!$options['full_possible']) {
            $builder->remove('full');
            $builder->add('factory', LocationChoiceType::class, [
                'filters' => $options['filters'],
                'label' => 'fields.factory',
            ]);
        } else {
            $builder->add('factory', LocationChoiceType::class, [
                'filters' => $options['filters'],
                'label' => 'fields.factory',
                'multiple' => true,
            ]);
        }

        if (!$options['export']) {
            $builder->remove('from');
            $builder->remove('to');

            $builder->add('date', MonthPickerType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month',
                ],
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => 'now',
                ],
                'label' => 'fields.date',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'filters' => [],
            'full_possible' => false,
            'export' => true,
        ]);
    }
}
