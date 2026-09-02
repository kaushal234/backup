<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomePeopleSearchChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'id_key' => 'id',
                'redirect_route' => 'directory_people_show',
                'redirect_route_params_map' => ['id' => 'id'],
            ]);
    }

    public function getParent(): string
    {
        return PeopleAdvancedChoiceType::class;
    }
}
