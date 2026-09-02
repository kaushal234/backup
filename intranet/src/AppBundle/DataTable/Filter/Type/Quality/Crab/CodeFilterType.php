<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Quality\Crab;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Quality\Crab\CodeChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodeFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CodeChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => \sprintf('%s - %s', $resource['code'], $resource['description']),
            ])
        ;
    }
}
