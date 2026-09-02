<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\People;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitorFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CompetitorAutocompleteChoiceType::class,
                'active_filter_formatter' => static function (ApiData|array $resource) {
                    return \sprintf('%s', $resource['name']);
                },
            ])
        ;
    }
}
