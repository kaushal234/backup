<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Purchasing;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Purchasing\SupplierRanking\ExpertiseLevelAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpertiseLevelFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ExpertiseLevelAutocompleteChoiceType::class,
                'active_filter_formatter' => static function (ApiData|array $resource) {
                    return $resource['name'];
                },
            ])
        ;
    }
}
