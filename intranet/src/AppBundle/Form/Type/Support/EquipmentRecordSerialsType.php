<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class EquipmentRecordSerialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serials', CollectionType::class, [
                'label' => false,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'deletable row',
                    ],
                ],
                'entry_type' => EquipmentSerialsType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }
}
