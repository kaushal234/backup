<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Finance;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Finance\CurrencyAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CurrencyAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
