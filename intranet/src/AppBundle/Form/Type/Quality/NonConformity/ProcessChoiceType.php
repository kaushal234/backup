<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\NonConformity;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcessChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'non_conformity.fields.process',
                'translation_domain' => 'non_conformity',
                'uri' => 'quality/processes',
                'text_key' => '[category]',
                'query' => [
                    'order' => [
                        'category' => 'ASC',
                    ],
                ],
                'template' => '{{ category }} {{ description }}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
