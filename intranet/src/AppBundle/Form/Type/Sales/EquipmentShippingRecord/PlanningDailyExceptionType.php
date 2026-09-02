<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningDailyExceptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isCreate = null === ($options['data'] ?? null);
        $builder
            ->add('factory', HiddenType::class, [
                'data' => $options['factory'],
            ])
            ->add('date', DatePickerType::class, [
                'required' => false,
                'label' => 'fields.date',
                'translation_domain' => 'messages',
                'data' => $isCreate ? $options['date'] : $options['data']['date'],
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'label' => 'demo.fields.comment',
                'translation_domain' => 'demo',
                'data' => $isCreate ? 'Exception details' : $options['data']['comment'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
            'csrf_protection' => false,
            'factory' => null,
            'date' => (new \DateTime('today'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
        ]);
    }
}
