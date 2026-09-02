<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstArticleQualificationTagChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.tags',
                'translation_domain' => 'messages',
                'uri' => 'quality/first_article_qualification_tags',
                'text_key' => '[name]',
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
