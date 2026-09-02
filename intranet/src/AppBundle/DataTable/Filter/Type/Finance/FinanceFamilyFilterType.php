<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Finance;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Finance\FinanceFamilyAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinanceFamilyFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => FinanceFamilyAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
