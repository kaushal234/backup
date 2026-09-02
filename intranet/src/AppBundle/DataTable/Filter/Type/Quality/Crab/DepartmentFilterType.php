<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Quality\Crab;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Quality\Crab\CrabDepartmentChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CrabDepartmentChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
