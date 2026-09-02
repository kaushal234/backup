<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\SupportTeam;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportTeamChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.premise.fields.support_team',
                'translation_domain' => 'directory',
                'uri' => 'mis/support_teams',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                ],
                'text_key' => '[name]',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
