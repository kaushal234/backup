<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportLevelChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'trouble_ticket.fields.support_level',
                'uri' => 'mis/support_levels',
                'query' => [
                    'order' => [
                        'level' => 'ASC',
                    ],
                ],
                'template' => 'Level {{level}} - {{name}}',
                'js_template_result' => 'mis/trouble_ticket/partial/_autocomplete_support_level.html.twig',
                'translation_domain' => 'trouble_ticket',
            ])
        ;
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
