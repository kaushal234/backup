<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitOperationalStatusAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'home.quick_search.people_label',
                'uri' => 'unit_operational_statuses',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'template' => '{{name}} - {{description}}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
