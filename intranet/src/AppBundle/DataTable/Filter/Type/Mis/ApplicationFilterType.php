<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Mis;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Mis\Application\ApplicationChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ApplicationChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
