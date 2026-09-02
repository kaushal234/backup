<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ProductAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
