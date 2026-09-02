<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Mis;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\AbstractAutocompleteFilterType;
use AppBundle\Form\Type\Mis\SupportTeam\SupportTeamChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportTeamFilterType extends AbstractAutocompleteFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.premise.fields.support_team',
                'translation_domain' => 'directory',
                'form_type' => SupportTeamChoiceType::class,
                'active_filter_formatter' => static fn (ApiData|array $resource) => $resource['name'],
            ])
        ;
    }
}
