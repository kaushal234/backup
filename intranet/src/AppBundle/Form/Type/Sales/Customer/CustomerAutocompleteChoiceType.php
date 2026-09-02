<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'sales/customers',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'hidden' => 0,
                    'normalization_groups_override' => ['customer_list'],
                ],
                'template' => '{{name}}',
                'js_template_result' => 'sales/customers/partial/_customer_search.html.twig',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
