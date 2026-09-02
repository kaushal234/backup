<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Legal;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Legal\CategoryAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CategoryAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
