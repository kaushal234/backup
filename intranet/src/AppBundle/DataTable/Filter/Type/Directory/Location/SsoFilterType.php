<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\Location;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SsoFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => SSOChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
