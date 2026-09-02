<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningDailyLimitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('factory', FactoryChoiceType::class, [
                'required' => null === ($options['data'] ?? null),
                'label' => 'directory.department.fields.factory',
                'disabled' => null !== ($options['data'] ?? null),
                'translation_domain' => 'directory',
            ])
            ->add('days', IntegerType::class, [
                'required' => true,
                'label' => 'planning_daily_limit.limit',
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'label' => 'demo.fields.comment',
                'translation_domain' => 'demo',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
            'csrf_protection' => false,
        ]);
    }
}
