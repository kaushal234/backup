<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Quality;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Quality\NonConformity\ResponsibleAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponsibleFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ResponsibleAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
