<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstArticleQualificationAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'crab.fields.first_article_qualification',
                'translation_domain' => 'crab',
                'uri' => 'quality/first_article_qualifications',
                'query' => [
                    'order' => [
                        'id' => 'ASC',
                    ],
                ],
                'text_key' => '[id]',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
