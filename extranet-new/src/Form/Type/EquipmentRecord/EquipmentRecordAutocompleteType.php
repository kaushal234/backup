<?php

declare(strict_types=1);

namespace App\Form\Type\EquipmentRecord;

use App\Form\Type\AutocompleteType;
use App\Sdk\Resource\EquipmentRecord;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentRecordAutocompleteType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route_name' => 'autocomplete_equipments',
            'resource_class' => EquipmentRecord::class,
            'placeholder' => 'extranet.placeholder.equipment_record',
            'min_characters' => 4,
            'choice_label' => function ($choice) {
                if ($choice instanceof EquipmentRecord) {
                    return $this->translator->trans('extranet.form.equipment_record_label', [
                        '%serial%' => $choice->serialNumber,
                        '%asset%' => $choice->customerSerialNumber,
                    ]);
                }

                return $choice;
            },
        ]);
    }

    public function getParent(): string
    {
        return AutocompleteType::class;
    }
}
