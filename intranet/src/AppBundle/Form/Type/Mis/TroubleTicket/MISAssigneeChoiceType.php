<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Directory\People\MISAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MISAssigneeChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'id_key' => 'id',
            'redirect_route' => 'trouble_ticket_assign_to_team',
            'redirect_route_params_map' => ['id' => 'assigneeId'],
        ]);
    }

    public function getParent(): string
    {
        return MISAutocompleteChoiceType::class;
    }
}
