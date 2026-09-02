<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory\People;

use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Directory\People\BuyerAutocompleteChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyerFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => BuyerAutocompleteChoiceType::class,
            ])
        ;
    }

    public function getParent(): ?string
    {
        return PeopleFilterType::class;
    }
}
