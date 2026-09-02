<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Mis;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Mis\FeatureChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeatureFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => FeatureChoiceType::class,
                'active_filter_formatter' => static function (ApiData|array $resource) {
                    return $resource['name'];
                },
            ])
        ;
    }
}
