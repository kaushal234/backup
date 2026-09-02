<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtranetUserAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'contacts.extranet_user',
                'uri' => 'sales/extranet_users',
                'query' => [
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                    'normalization_groups_override' => ['extranet_user_list'],
                    'extranetUserProfile.archived' => 0,
                    'hidden' => 0,
                ],
                'template' => '{{lastname}} {{firstname}}',
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
