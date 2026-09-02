<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApprovedCustomerAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'sales/customers',
                'text_key' => '[name]',
                'query' => [
                    'status' => ['APPROVED', 'PENDING RE-APPROVAL'],
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
