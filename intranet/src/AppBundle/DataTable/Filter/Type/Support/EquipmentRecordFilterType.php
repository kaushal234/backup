<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Support;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => EquipmentRecordAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['serialNumber'],
            ])
        ;
    }
}
