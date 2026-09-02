<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Purchasing;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimStatusAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimStatusFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => VendorWarrantyClaimStatusAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
