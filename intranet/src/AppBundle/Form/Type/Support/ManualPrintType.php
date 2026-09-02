<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManualPrintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $print = $builder->getData();

        $builder
            ->add('manualPrinter', ManualPrinterChoiceType::class, [
                'label' => 'support.printer.printer',
            ])
            ->add('requestedDeliveryDate', DatePickerType::class, [
                'label' => 'support.manual_print.fields.requestedDeliveryDate',
                'data' => $print['requestedDeliveryDate'] ?? date(DatePickerType::DEFAULT_INPUT_FORMAT),
            ])
            ->add('standard', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 0],
                'data' => 0,
                'label' => 'support.manual_print.fields.standard',
            ])
            ->add('full', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 0],
                'data' => 0,
                'label' => 'support.manual_print.fields.full',
            ])
            ->add('extra', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 0],
                'data' => 0,
                'label' => 'support.manual_print.fields.extra',
            ])
            ->add('chapter5', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 0],
                'data' => 0,
                'label' => 'support.manual_print.fields.chapter5',
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'label' => 'support.manual_print.fields.comment',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'support.manual_print.button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'support',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
        ]);
    }
}
