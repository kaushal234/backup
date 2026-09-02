<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Mis\TroubleTicket;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Mis\TroubleTicket\TypeAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => TypeAutocompleteChoiceType::class,
                'active_filter_formatter' => static function (ApiData|array $resource) {
                    return \sprintf('%s - %s', $resource['type'], $resource['description']);
                },
            ])
        ;
    }
}
