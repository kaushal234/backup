<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'equipment_records',
                'query' => [
                    'order' => [
                        'serialNumber' => 'ASC',
                    ],
                ],
                'template' => '{{ serialNumber }} - {{ model }} - {{ type }}',
                'js_template_result' => 'partial/_equipment_record_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
