<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\Position;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Directory\Position\PositionAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => PositionAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['description'],
            ])
        ;
    }
}
