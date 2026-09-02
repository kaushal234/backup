<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'label' => false,
                'placeholder' => 'form.placeholder.search_equipment_record',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }
}
