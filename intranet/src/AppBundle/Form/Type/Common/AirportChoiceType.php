<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirportChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'airports',
                'query' => [
                    'normalization_groups_override' => ['airport_list'],
                    'order' => ['code' => 'ASC'],
                ],
                'template' => '{{ code }} - {{ cityName }}',
                'js_template_result' => 'partial/_airport_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
