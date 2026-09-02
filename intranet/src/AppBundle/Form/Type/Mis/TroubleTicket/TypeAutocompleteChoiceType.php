<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'trouble_ticket.fields.type',
                'uri' => 'mis/types',
                'query' => [
                    'order' => [
                        'displayedOrder' => 'ASC',
                    ],
                ],
                'template' => '{{type}} - {{description}}',
                'js_template_result' => 'mis/trouble_ticket/partial/_autocomplete_type.html.twig',
                'translation_domain' => 'trouble_ticket',
            ])
        ;
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
