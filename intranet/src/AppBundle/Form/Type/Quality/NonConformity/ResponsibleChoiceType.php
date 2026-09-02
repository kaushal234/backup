<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\NonConformity;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponsibleChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'non_conformity.fields.responsible',
                'translation_domain' => 'non_conformity',
                'uri' => 'quality/responsibles',
                'text_key' => 'name',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
