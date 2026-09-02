<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Parts;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Parts\SparePartRequestDeliveryAddressAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SparePartRequestDeliveryAddressFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'form_type' => SparePartRequestDeliveryAddressAutocompleteChoiceType::class,
            'active_filter_formatter' => static fn (ApiData|array $resource) => \sprintf(
                '%s, %s %s',
                $resource['address']['street1'],
                $resource['address']['postalCode'],
                $resource['address']['city'],
            ),
        ]);
    }
}
