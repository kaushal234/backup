<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Project;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'mis/project_tags',
                'text_key' => '[name]',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
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
