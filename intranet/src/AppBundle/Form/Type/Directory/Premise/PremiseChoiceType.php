<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Premise;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PremiseChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.premise.title',
                'translation_domain' => 'directory',
                'uri' => 'premises',
                'query' => [
                    'archived' => false,
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'template' => '{{name}}: {{description}} - {{address.country}}',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
