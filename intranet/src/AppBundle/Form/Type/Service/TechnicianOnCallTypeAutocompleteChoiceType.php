<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallTypeAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'service/technician_on_call_types',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'template' => '{{description}}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
