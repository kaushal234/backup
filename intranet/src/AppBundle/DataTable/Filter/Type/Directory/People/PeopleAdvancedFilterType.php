<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\People;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Directory\People\PeopleAdvancedChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleAdvancedFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => PeopleAdvancedChoiceType::class,
                'active_filter_formatter' => static function (ApiData|array $resource) {
                    return \sprintf('%s %s', $resource['lastname'], $resource['firstname']);
                },
            ])
        ;
    }
}
