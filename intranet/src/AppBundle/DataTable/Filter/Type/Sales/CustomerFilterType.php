<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CustomerAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
