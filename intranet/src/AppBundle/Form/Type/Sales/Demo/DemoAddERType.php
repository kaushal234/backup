<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Demo;

use AppBundle\Form\Type\Support\EquipmentRecordChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemoAddERType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipmentRecord', EquipmentRecordChoiceType::class, [
                'er_demo' => true,
                'product' => $options['product'],
                'label' => 'demo.fields.equipment_record',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'demo',
            'product' => null,
        ]);
    }
}
