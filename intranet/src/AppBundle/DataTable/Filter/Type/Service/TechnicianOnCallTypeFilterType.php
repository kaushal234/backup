<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Service;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Service\TechnicianOnCallTypeAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallTypeFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => TechnicianOnCallTypeAutocompleteChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
