<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SparePartRequestDeliveryAddressAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'parts/spare_parts_request_delivery_addresses',
                'template' => '{{ address.street1 }}, {{ address.postalCode }} {{ address.city }}',
                'js_template_result' => 'partial/_address_with_country_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
