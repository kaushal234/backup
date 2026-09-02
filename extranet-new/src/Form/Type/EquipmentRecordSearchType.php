<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\EquipmentRecord\EquipmentRecordAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EquipmentRecordSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('equipmentRecord', EquipmentRecordAutocompleteType::class, [
            'label' => false,
        ]);
    }
}
