<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Common\AirportChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirportFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => AirportChoiceType::class,
                'active_filter_formatter' => static function (ApiData|array $resource) {
                    return \sprintf('%s - %s', $resource['code'], $resource['cityName']);
                },
            ])
        ;
    }
}
